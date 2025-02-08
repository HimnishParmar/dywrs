<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | DyWRS</title>
    <style>
        *{
    margin: 0;
    padding: 0;
}
html, body{
    overflow: hidden;
}

.heading{
    font-size: 10vh;
    font-weight: 600;
    color: white;
    font-family: "Poppins" cursive;
}

.background{
    height: 100vh;
    width: 100vw;
    position: absolute;
    background: url({{asset('assets/img/auth/login_bg.jpg')}});
    background-position: center;
    background-size: cover;
    filter: blur(5px);
    z-index: -1;
}
.main-body{
    height: 100vh;
    width: 100vw;
    display: flex;
    justify-content: center;
    align-items: center;
}
.sign-in-card{
    height: 70vh;
    width: 80%;
    display: flex;
    border-radius: 20px;
    background-color: white;
    box-shadow: 1px 1px 5px 4px rgb(78, 128, 78);
}
.inner-left{
    height: 100%;
    width: 50%;
    background-image: url({{asset('assets/img/auth/login_bg.jpg')}});
    background-size: cover;
    background-position: center;
    /* filter: blur(1.5px); */
    border-top-left-radius: 20px;
    border-bottom-left-radius: 20px;   
    display: flex;
    justify-content: center; 
    align-items: center;  
}


.inner-right{
    height: 100%;
    width: 50%;
    background-color: rgb(204, 248, 207);
    border-top-right-radius: 20px;
    border-bottom-right-radius: 20px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    font-family: cursive;
}
.sign-in-form{
    height: 50%;
    width: 50%;
    display: flex;
    flex-direction: column;
    justify-content: space-evenly;
    font-family: Arial, Helvetica, sans-serif;
}
.input-field{
    padding-left: 5px;
    height: 30px;
    outline: none;
    border-color: transparent;
    /* border-radius: 20px; */
    /* box-shadow: 1px 1px 5px 4px rgb(78, 128, 78); */
}
.sign-up{
    display: flex;
    justify-content: space-between;
    font-size: 14px;
}
#login-btn{
    height: 12%;
    align-self: center;
    width: 50%;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 700;
    outline: none;
    cursor: pointer;
    background: linear-gradient(to left, green, rgb(213, 253, 211));
}
        </style>
</head>
<body>
    <div class="background"></div>
    <div class="main-body">
        <div class="sign-in-card">
            <div class="inner-left"><span class="heading">DyWRS</span></div>
            <div class="inner-right">
                <h1>Welcome Back!</h1>
                    <form class="sign-in-form"  method="post"  action="{{ route('login') }}">
                        @csrf
                        <label for="uname"><b>Username</b></label>
                        <input type="text" " id="username" placeholder="Username"  class="input-field @error('username') is-invalid @enderror" name="username" value="{{ old('username') }}" required autocomplete="username" autofocus>
                        @error('username')
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                                @enderror
                        <label for="psw"><b>Password</b></label>
                        <input type="password" class="input-field @error('password') is-invalid @enderror" placeholder="Password" name="password" required autocomplete="current-password">
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                        <div class="sign-up">
                            <p>New to DyWRS?</p><a href="{{route('register')}}">Sign up</a>
                        </div>
                        <button type="submit" id="login-btn">Login</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>