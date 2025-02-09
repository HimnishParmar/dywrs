import os
import cv2
import numpy as np
import torch
import torchvision.transforms as transforms
from flask import Flask, request, jsonify, render_template, send_file
from PIL import Image
from rembg import remove
from transformers import AutoFeatureExtractor, AutoModelForImageClassification
import matplotlib.pyplot as plt
import math
import cvzone
from ultralytics import YOLO

# Load YOLO model with custom weights
yolo_model = YOLO("Weights/last.pt")
yolo_class_labels = ['0', 'c', 'garbage', 'garbage_bag', 'sampah-detection', 'trash']

# Load MiDaS model for depth estimation
midas_model = torch.hub.load("intel-isl/MiDaS", "DPT_Large")
midas_model.eval()
device = torch.device("cuda" if torch.cuda.is_available() else "cpu")
midas_model.to(device)

# Load pre-trained waste classification model
MODEL_NAME = "yangy50/garbage-classification"
model = AutoModelForImageClassification.from_pretrained(MODEL_NAME)
extractor = AutoFeatureExtractor.from_pretrained(MODEL_NAME)
LABELS = ["cardboard", "glass", "metal", "paper", "plastic", "trash"]

# Flask app
app = Flask(__name__, static_url_path='/debug', static_folder='debug_images')

# Directories for uploads and debugging
UPLOAD_DIR = "uploads"
DEBUG_DIR = "debug_images"
ORIGINAL_DIR = "originals"
os.makedirs(UPLOAD_DIR, exist_ok=True)
os.makedirs(DEBUG_DIR, exist_ok=True)
os.makedirs(ORIGINAL_DIR, exist_ok=True)

# Global variables
scale_factor = None
DEPTH_SCALE = 40
COIN_SIZES = {"1": 0.02125, "2": 0.027, "5": 0.023}


@app.route("/debug_image/<filename>")
def get_debug_image(filename):
    # Path to the image in the debug_images folder
    image_path = os.path.join(DEBUG_DIR, filename)
    
    # Check if the file exists
    if os.path.exists(image_path):
        return send_file(image_path, mimetype='image/png')
    else:
        return jsonify({"error": "Image not found"}), 404
    
    
def save_debug_image(image, name):
    """ Save debug images for reference. """
    debug_path = os.path.join(DEBUG_DIR, name)
    cv2.imwrite(debug_path, image)
    return debug_path

def classify_waste(image_path):
    if not os.path.exists(image_path):
        raise FileNotFoundError(f"The image file {image_path} does not exist.")
    
    image = Image.open(image_path).convert("RGB")
    inputs = extractor(images=image, return_tensors="pt")
    with torch.no_grad():
        outputs = model(**inputs)
        predicted_class = outputs.logits.argmax(-1).item()
    return LABELS[predicted_class]

def estimate_depth(image_path):
    image = Image.open(image_path).convert("RGB")
    input_tensor = transforms.ToTensor()(image).unsqueeze(0).to(device)
    with torch.no_grad():
        depth_map = midas_model(input_tensor).squeeze().cpu().numpy()

    depth_map = (depth_map - depth_map.min()) / (depth_map.max() - depth_map.min())
    
    # Save depth map debug image
    plt.imsave(os.path.join(DEBUG_DIR, "depth_map.png"), depth_map, cmap="inferno")
    
    return depth_map

def calibrate_with_coin(image_path, coin_type):
    global scale_factor
    coin_diameter = COIN_SIZES.get(coin_type)
    if not coin_diameter:
        return "Invalid coin type. Choose from 1, 2, or 5 INR."
    
    img = cv2.imread(image_path)
    gray = cv2.cvtColor(img, cv2.COLOR_BGR2GRAY)
    
    # Save grayscale debug image
    save_debug_image(gray, "coin_gray.png")
    
    circles = cv2.HoughCircles(gray, cv2.HOUGH_GRADIENT, 1.2, 50, param1=50, param2=30, minRadius=10, maxRadius=100)
    if circles is not None:
        pixel_diameter = 2 * np.uint16(np.around(circles))[0, 0, 2]
        scale_factor = (coin_diameter / pixel_diameter) * 100  # cm/pixel

        # Draw detected circle
        circle_img = img.copy()
        x, y, r = np.uint16(np.around(circles))[0, 0]
        cv2.circle(circle_img, (x, y), r, (0, 255, 0), 2)
        save_debug_image(circle_img, "coin_detected.png")

        return circle_img, f"Calibration successful! Scale Factor: {scale_factor:.6f} cm/pixel"
    
    return None, "Coin not detected! Retry with a clearer image."

def estimate_space_required(image_path, depth_map):
    global scale_factor
    if scale_factor is None:
        return None, "Calibration required! Upload a coin image first."
    
    object_mask = remove_background(image_path)

    contours, _ = cv2.findContours(object_mask, cv2.RETR_EXTERNAL, cv2.CHAIN_APPROX_SIMPLE)
    if not contours:
        return None, "No object detected!"

    x, y, w, h = cv2.boundingRect(max(contours, key=cv2.contourArea))
    real_width = w * scale_factor
    real_height = h * scale_factor
    depth_cm = np.median(depth_map[depth_map > 0]) * scale_factor * DEPTH_SCALE

    # Save bounding box debug image
    img = cv2.imread(image_path)
    cv2.rectangle(img, (x, y), (x + w, y + h), (0, 255, 0), 2)
    save_debug_image(img, "object_bounding_box.png")

    return {
        "length_cm": round(real_height, 2),
        "width_cm": round(real_width, 2),
        "height_cm": round(depth_cm, 2),
        "estimated_space_cm_cubed": round(real_height * real_width * depth_cm, 2),
        "image" : 'object_bounding_box.png'
    }, None

def remove_background(image_path):
    image = Image.open(image_path).convert("RGBA")
    output_image = remove(image)

    gray = cv2.cvtColor(np.array(output_image), cv2.COLOR_RGBA2GRAY)
    _, object_mask = cv2.threshold(gray, 1, 255, cv2.THRESH_BINARY)

    # Save object mask debug image
    save_debug_image(object_mask, "object_mask.png")

    return object_mask

def garbage_detection(image_path):
    img = cv2.imread(image_path)
    results = yolo_model(img)
    detections = []

    for r in results:
        boxes = r.boxes
        for box in boxes:
            x1, y1, x2, y2 = box.xyxy[0]
            x1, y1, x2, y2 = int(x1), int(y1), int(x2), int(y2)

            w, h = x2 - x1, y2 - y1
            conf = math.ceil((box.conf[0] * 100)) / 100
            cls = int(box.cls[0])

            if conf > 0.3:
                cvzone.cornerRect(img, (x1, y1, w, h), t=2)
                cvzone.putTextRect(img, f'{yolo_class_labels[cls]} {conf}', (x1, y1 - 10), scale=0.8, thickness=1, colorR=(255, 0, 0))
                detections.append(yolo_class_labels[cls])

    # Save detection result image and return the path
    detection_image_path = save_debug_image(img, "garbage_detection.png")
    return detections, detection_image_path

@app.route("/", methods=["GET"])
def home():
    return render_template('index.html')


@app.route("/calibrate", methods=["POST"])
def upload_coin():
    file = request.files["image"]
    coin_type = request.form.get("coin_type")
    if not file or not coin_type:
        return jsonify({"error": "Missing image or coin_type (1, 2, or 5)"}), 400

    file_path = os.path.join(UPLOAD_DIR, file.filename)
    file.save(file_path)
    result, message = calibrate_with_coin(file_path, coin_type)

    if isinstance(result, str) and result:
        debug_image_url = f"/debug_image/{os.path.basename(result)}"  # Update URL
    elif isinstance(result, np.ndarray) and result.size > 0:
        debug_image_url = f"/debug_image/{os.path.basename('coin_detected.png')}"
        cv2.imwrite(f"debug_images/coin_detected.png", result)  # Save new image
    else:
        debug_image_url = None

    return jsonify({
        "message": message,
        "scale_factor": scale_factor,
        "image_url": debug_image_url  # Provide the image URL here
    })



@app.route("/hasGarbage", methods=["POST"])
def has_garbage():
    file = request.files["image"]
    if not file:
        return jsonify({"error": "No image provided"}), 400

    file_path = os.path.join(DEBUG_DIR, "original.png")
    file.save(file_path)

    result, message = garbage_detection(file_path)  # Assume detect_garbage returns an image or result
    debug_image_path = os.path.join(DEBUG_DIR, "garbage_detected.png")
    
    
    if isinstance(result, np.ndarray) and result.size > 0:
        cv2.imwrite(debug_image_path, result)

    return jsonify({
        "message": message,
        "error" : False,
        "success" : True,
        "image_url": debug_image_path,
        "original" : f"/debug_image/{os.path.basename('original.png')}",
    })



@app.route("/classify_waste", methods=["POST"])
def classify():
    file = request.files["image"]
    if not file:
        return jsonify({"error": "No image uploaded"}), 400

    file_path_o = os.path.join(ORIGINAL_DIR, "classify"  + file.filename)
    file.save(file_path_o)

    waste_type = classify_waste(file_path_o)
    
    new_path = f"/debug_image/{os.path.basename('original.png')}"
    
    return jsonify({"waste_type": waste_type, "error" : False, "original_image_url" : new_path})

@app.route("/estimate", methods=["POST"])
def upload_image():
    file = request.files["image"]
    if not file:
        return jsonify({"error": "No image uploaded"}), 400

    file_path = os.path.join(UPLOAD_DIR, "space"  + file.filename)
    file.save(file_path)

    depth_map = estimate_depth(file_path)
    space_estimate, error = estimate_space_required(file_path, depth_map)

    if error:
        return jsonify({"error": error}), 400

    # Sending space estimate and debug images URL
    debug_image_url = f"/debug/{os.path.basename(space_estimate['image'])}"
    depth_image_url = f"/debug_image/{os.path.basename('depth_map.png')}"
    objectBounding_image_url = f"/debug_image/{os.path.basename('object_bounding_box.png')}"
    objectMask_image_url = f"/debug_image/{os.path.basename('object_mask.png')}"
   
    if(space_estimate['estimated_space_cm_cubed'] > 2500) :
        space_estimate_range = "Very High Volume"    
    elif(space_estimate['estimated_space_cm_cubed'] > 2000):
        space_estimate_range = "High Volume"    
    elif(space_estimate['estimated_space_cm_cubed'] > 1200):
        space_estimate_range = "Medium"    
    elif(space_estimate['estimated_space_cm_cubed'] > 100):
        space_estimate_range = "Low Volume"    
    else:
        space_estimate_range = "Very Low Volume"    
        
    
    return jsonify({
        "space_estimate": space_estimate_range,
        'debug_image_url' : debug_image_url,
        'depth' : depth_image_url,
        'objectBounding' : objectBounding_image_url,
        'objectMask' : objectMask_image_url
    })


if __name__ == "__main__":
    app.run(debug=True)
