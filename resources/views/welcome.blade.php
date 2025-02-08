<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="{{asset('assets/css/index.css')}}">

        <style>
                        
            .page {
                background: url('{{ asset('assets/img/index/new.jpg') }}'), linear-gradient(to bottom, rgba(0, 0, 0, 0) 70%, rgba(0, 0, 0, 1) 99%);
                background-repeat: no-repeat;
                background-position: center;
                background-size: cover;
                background-blend-mode: color-dodge;
            }
        </style>
    </head>
    <body class="font-sans antialiased dark:bg-black dark:text-white/50">
        <modal name="recycler">
            <modal-content>
                <recycle-modal>
                        <div onclick="recycleModal.hide(300)" style="color: aliceblue">Close</div>
    
                        <r-modal-section>
                            <r-sections>
                            <section>
                                <img src="{{ asset('assets/img/index/ondmd.jpg') }}" width="400px"/>
                                <parent-center>
                                    <p style="color: aliceblue">On Demand</p>
                                    <a  style="color: aliceblue" href="{{route('waste.submit', ['type' => "ondmd"])}}" >Start</a>
                                </parent-center>
                            </section>
                            <section>
                                <img src="{{ asset('assets/img/index/eco.jpg') }}" width="400px"/>
                                <parent-center>
                                    <p style="color: aliceblue">Swachhch Bharat (Eco-Friendly)</p>
                                    <a  style="color: aliceblue" href="{{route('waste.submit', parameters: ['type' => "eco"])}}">Start</a>
                                </parent-center>
                            </section>
                            </r-sections>
                            <r-tag>Want to subcribe for wate pickup? <a href="#"> Click Here </a></r-tag>
                        </r-modal-section>
                </recycle-modal>
            </modal-content>
        </modal>
        <div class="floating-header">
            <header>
                <div>
                    <logo>
                        <logo-text>DyWRS</logo-text>
                    </logo>
                </div>
                <div>
                    <nav>
                        <menu-bar>
                            <menu>About</menu>
                            <menu>How We work?</menu>
                            <menu>Services</menu>
                            <menu>Marketplace</menu>
                            <menu>3R</menu>
                        </menu-bar>
                    </nav>
                </div>
                <div>
                    <menu-bar>
                        <menu class="book" onclick="recycleModal.show(500)">Recycle Now</menu>
                        @if(Auth::check())
                        <a href="{{route('home')}}" style="text-decoration: none; display: flex; align-items: center;">    
                        <menu style="padding: 0; margin: 0; display: flex; align-items: center;">
                            <svg width="40px" height="40px" viewBox="0 0 24.00 24.00" fill="none" xmlns="http://www.w3.org/2000/svg" stroke="#000" stroke-width="2.4" transform="rotate(0)matrix(1, 0, 0, 1, 0, 0)"><g id="SVGRepo_bgCarrier" stroke-width="0"></g><g id="SVGRepo_tracerCarrier" stroke-linecap="round" stroke-linejoin="round" stroke="transparent" stroke-width="0.192"></g><g id="SVGRepo_iconCarrier"> <path fill-rule="evenodd" clip-rule="evenodd" d="M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12ZM15 9C15 10.6569 13.6569 12 12 12C10.3431 12 9 10.6569 9 9C9 7.34315 10.3431 6 12 6C13.6569 6 15 7.34315 15 9ZM12 20.5C13.784 20.5 15.4397 19.9504 16.8069 19.0112C17.4108 18.5964 17.6688 17.8062 17.3178 17.1632C16.59 15.8303 15.0902 15 11.9999 15C8.90969 15 7.40997 15.8302 6.68214 17.1632C6.33105 17.8062 6.5891 18.5963 7.19296 19.0111C8.56018 19.9503 10.2159 20.5 12 20.5Z" fill="#fff"></path> </g></svg>
                        </menu> 
                        </a>
                        @else      
                        <a href="{{route('login')}}" style="text-decoration: none">    
                        <menu class="book" style=" display: flex; align-items: center;">
                             Login 
                        </menu>
                        </a>
                        @endif
                    </menu-bar>
                </div>
            </header>
        </div>
        <main>
            <page class="page">
                <flex-vw-center>
                    <hero-section>
                        <hero-heading>Revolutionizing Waste Management for Us 
                            <hero-content>
                                Effortless waste disposal, real-time tracking, and rewards for eco-conscious living.
                            </hero-content>
                            <a class="cta">
                                Recycle Now
                            </a>
                    </hero-section>
                </flex-vw-center>
            </page>
            <page class="page1">
                <main-section-7>
                    <main-heading>About Our Solution</main-heading>
                    <main-content>
                        Simplifying waste management for 
                        <br>
                        <span href="" class="typewrite" data-period="1000" data-type='[ "Us.", "Greener Future."]'>
                              <span class="wrap"></span>
                            </span>
                    </main-content>
                    <row>
                        <col-5>
                            <small>
                                A Perfect <br> Recycler
                            </small>
                        </col-5>
                        <col-7>
                            <main-sub-content>
                                Our platform empowers citizens with smarter, eco-friendly waste solutions. From dynamic home
                                waste pickups to real-time tracking and recycling incentives, we make waste management
                                hassle-free, transparent, and sustainable for everyone.
                            </main-sub-content>
                            <menu class="book" onclick="recycleModal.show(500)">
                                Recycle Now
                            </menu>
                        </col-7>
                    </row>
                </main-section-7>
            </page>
        </main>

       
        <script>
            var TxtType = function(el, toRotate, period) {
            this.toRotate = toRotate;
            this.el = el;
            this.loopNum = 0;
            this.period = parseInt(period, 10) || 2000;
            this.txt = '';
            this.tick();
            this.isDeleting = false;
        };
    
        TxtType.prototype.tick = function() {
            var i = this.loopNum % this.toRotate.length;
            var fullTxt = this.toRotate[i];
    
            if (this.isDeleting) {
            this.txt = fullTxt.substring(0, this.txt.length - 1);
            } else {
            this.txt = fullTxt.substring(0, this.txt.length + 1);
            }
    
            this.el.innerHTML = '<span class="wrap">'+this.txt+'</span>';
    
            var that = this;
            var delta = 200 - Math.random() * 100;
    
            if (this.isDeleting) { delta /= 2; }
    
            if (!this.isDeleting && this.txt === fullTxt) {
            delta = this.period;
            this.isDeleting = true;
            } else if (this.isDeleting && this.txt === '') {
            this.isDeleting = false;
            this.loopNum++;
            delta = 500;
            }
    
            setTimeout(function() {
            that.tick();
            }, delta);
        };
    
        window.onload = function() {
            var elements = document.getElementsByClassName('typewrite');
            for (var i=0; i<elements.length; i++) {
                var toRotate = elements[i].getAttribute('data-type');
                var period = elements[i].getAttribute('data-period');
                if (toRotate) {
                  new TxtType(elements[i], JSON.parse(toRotate), period);
                }
            }
            // INJECT CSS
            var css = document.createElement("style");
            css.type = "text/css";
            css.innerHTML = ".typewrite > .wrap { border-right: 0.08em solid #fff}";
            document.body.appendChild(css);
        };
        </script>
    </body>
</html>
