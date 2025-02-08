class History {
    constructor() {
        this.modals = {}; // Stores modal states, DOM references, and animations
    }

    addModal(name, modalElement) {
        if (!this.modals[name]) {
            this.modals[name] = {
                status: false,
                element: modalElement,
                animations: { in: [], out: [] }, // Default animations
            };
        }
    }

    getModal(name) {
        return this.modals[name] || null;
    }

    updateStatus(name, status) {
        if (this.modals[name]) {
            this.modals[name].status = status;
        }
    }

    setAnimations(name, animations) {
        if (this.modals[name]) {
            this.modals[name].animations = animations;
        }
    }
}

class ModelUtils {
constructor() {
    this.history = new History();
}

registerModal(name, defaultAnimations = { in: ['fadeIn'], out: ['fadeOut'] }) {
    const modalElement = document.querySelector(name) || document.querySelector(`[name=${name}]`);
    if (modalElement) {
        this.history.addModal(name, modalElement);
        this.history.setAnimations(name, defaultAnimations);
    }
}

showModal(name, duration = 300, animations = null) {
    const modal = this.history.getModal(name);
    if (modal && !modal.status) {
        const appliedAnimations = animations || modal.animations.in;

        // Create backdrop
        this.createBackdrop(modal.element, name);

        // Animate modal for showing
        this.animateModal(modal.element, appliedAnimations, duration, true, () => {
            modal.element.style.opacity = ''; // Reset inline opacity
            modal.element.style.transform = ''; // Reset inline transform
        });

        modal.element.classList.add('active'); // Add active class
        this.history.updateStatus(name, true); // Update status
    }
}

hideModal(name, duration = 300, animations = null) {
    const modal = this.history.getModal(name);
    if (modal && modal.status) {
        const appliedAnimations = animations || modal.animations.out;

        // Animate modal for hiding
        this.animateModal(modal.element, appliedAnimations, duration, false, () => {
            modal.element.classList.remove('active'); // Remove active class
            this.removeBackdrop();
        });

        this.history.updateStatus(name, false); // Update status
    }
}

toggleModal(name, duration = 300, animations = null) {
    const modal = this.history.getModal(name);
    if (modal) {
        if (modal.status) {
            this.hideModal(name, duration, animations?.out || modal.animations.out);
        } else {
            this.showModal(name, duration, animations?.in || modal.animations.in);
        }
    }
}

createBackdrop(element, name) {
    if (!element.querySelector('.modal-backdrop')) {
        const backdrop = document.createElement('div');
        backdrop.classList.add('modal-backdrop');
        element.appendChild(backdrop);

        // Close the modal when the backdrop is clicked
        backdrop.addEventListener('click', () => {
            this.hideModal(name); 
        });
    }
}

removeBackdrop() {
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        backdrop.remove();
    }
}

animateModal(element, animations, duration, isIn, callback = null) {
    const animationStyles = {
        fadeIn: { opacity: 1 },
        fadeOut: { opacity: 0 },
        slideTop: { transform: 'translateY(-100%)' },
        slideBottom: { transform: 'translateY(100%)' },
        slideLeft: { transform: 'translateX(-100%)' },
        slideRight: { transform: 'translateX(100%)' },
    };

    const resetStyles = {
        fadeIn: { opacity: 0 },
        fadeOut: { opacity: 1 },
        slideTop: { transform: 'translateY(0)' },
        slideBottom: { transform: 'translateY(0)' },
        slideLeft: { transform: 'translateX(0)' },
        slideRight: { transform: 'translateX(0)' },
    };

    // Apply initial animation styles
    animations.forEach((anim) => {
        if (resetStyles[anim]) {
            Object.assign(element.style, resetStyles[anim]);
        }
    });

    // Trigger animations
    element.style.transition = `all ${duration}ms ease-in-out`;
    setTimeout(() => {
        animations.forEach((anim) => {
            if (animationStyles[anim]) {
                Object.assign(element.style, animationStyles[anim]);
            }
        });
    }, 10);

    // Cleanup after animation
    setTimeout(() => {
        element.style.transition = ''; // Reset transition
        if (callback) callback();
    }, duration);
}
}


class Modal {
    constructor(name, options = {}) {
        this.utils = new ModelUtils(); // Initialize ModelUtils
        this.name = name;

        // Set default options with fallback
        this.options = {
            animate: {
                in: options.animate?.in || 'fadeIn',
                out: options.animate?.out || 'fadeOut',
            },
            backdrop: options.backdrop ?? true,
            autoClose: options.autoClose || 0,
            closeOnBackdropClick: options.closeOnBackdropClick ?? true,
        };

        // Automatically register this modal with animations
        const modalElement = document.querySelector(name) || document.querySelector(`[name=${name}]`);
        if (modalElement) {
            this.utils.history.addModal(name, modalElement);
            this.utils.history.setAnimations(name, {
                in: this.options.animate.in.split(' '),
                out: this.options.animate.out.split(' '),
            });

            // Add a listener to reset styles after animations
            modalElement.addEventListener('animationend', (event) => {
                if (event.animationName === this.options.animate.out) {
                    modalElement.style.opacity = '0'; // Ensure modal is hidden
                    modalElement.style.transform = ''; // Reset any transforms
                }
            });
        }
    }

    show(duration = 300) {
        const modalElement = this.utils.history.getModal(this.name)?.element;
        if (!modalElement) return;

        // Reset styles before applying entry animation
        modalElement.style.opacity = '';
        modalElement.style.transform = '';

        this.utils.showModal(this.name, duration);
        this.options.animate.in.split(' ').forEach((animation) => {
            modalElement.classList.add(animation);
        });

        if (this.options.autoClose > 0) {
            setTimeout(() => this.hide(), this.options.autoClose);
        }
    }

    hide(duration = 300) {
        const modalElement = this.utils.history.getModal(this.name)?.element;
        if (!modalElement) return;

        // Add exit animation
        this.options.animate.out.split(' ').forEach((animation) => {
            modalElement.classList.add(animation);
        });

        // Remove entry animation class and hide modal after exit
        setTimeout(() => {
            this.options.animate.in.split(' ').forEach((animation) => {
                modalElement.classList.remove(animation);
            });
            this.options.animate.out.split(' ').forEach((animation) => {
                modalElement.classList.remove(animation);
            });

            this.utils.hideModal(this.name, duration);
            modalElement.style.opacity = '0'; // Ensure modal is hidden
        }, duration);
    }

    toggle(duration = 300) {
        const modal = this.utils.history.getModal(this.name);
        if (modal?.status) {
            this.hide(duration);
        } else {
            this.show(duration);
        }
    }
}


const recycleModal = new Modal('recycler', {
    animate: {
        in: 'fadeIn',
        out: 'fadeOut',
    }
});
    
function modal(){
    recycleModal.toggle();
}
