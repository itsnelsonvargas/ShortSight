<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x:head></x:head>
    </head>
    <body class="antialiased">
    
    <div class="container-fluid">



        <div class="row">
            <div class="col h-25">

                <h1 class="text-center p-5">URL Shortener</h1>

            </div>
        </div>


        <div class="row">
            <div class="col-md-12 ">
                
            </div>
        </div>  

        <hr style="background-color:white;">

        <div class="row">

            <div class="col-md-4 col-sm-12">
                 
            </div><!--left col-md-4-->

            <div class="col-md-4 col-sm-12">

                <div class="card form-bg">

                    <div class="card-header">
                        
                        <h3 class="text-center">Shorten a URL<i class="fa fa-link" aria-hidden="true"></i></h3>  

                        <!-- Display the message from the controller --> 
                        @if(isset($newSlug))
                            <div class="alert alert-success">
                                <p>You may now access the shortened link via: 
                                    <a href="{{ $submittedUrl }}"> 
                                        <strong>{{ env('APP_URL') . '/' . $newSlug  }}</strong>
                                    </a>
                                </p>
                                
                                <button class="btn  btn-outline-success" onclick="copyToClipboard('{{ env('APP_URL') . '/' . $newSlug }}')"> 
                                    <i class="fa fa-copy "></i> Copy
                                </button>
                            </div>
                        @endif

                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('createLinkWithoutUserAccount') }}">
                            @csrf
                            @method('POST')
                            <div class="form-group
                            @error('url') has-error @enderror">
                                <label for="url">URL</label>
                                <i class="fa-solid fa-circle-check check-icon "></i>  
                            
                                <input type="text" class="form-control" id="url" name="url" value=" ">
                                @error('url')
                                    <span class="help-block
                                    text-danger">{{ $message }}</span>
                                @enderror 
                            </div> 
                    </div>

                    <div class="card-footer">
                            <button type="submit" class="btn btn-outline-yellow  ">Shorten</button>  
                        </form>
                    </div>

                </div><!--card-->

            </div><!--center col-md-4-->

            <div class="col-md-2 col-sm-12">
            </div>

            <div class="col-md-2 col-sm-12">
                @if(Auth::check())
                    <p>Welcome, {{ Auth::user()->name }}!</p>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        @method('GET')
                        <button type="submit" class="btn btn-danger">
                            <i class="fa fa-sign-out" aria-hidden="true"></i> Logout
                        </button>
                    </form>
                @else
                 
                    <div class="card form-bg">
                        <div class="card-header">
                            <h3 class="text-center">Login</h3>
                            <small>To track and manage your shorten link. Contains visitor's </small>
                        </div>
                        <div class="card-body">
                            <form method="POST" action=" ">
                                @csrf
                                <div class="form-group
                                @error('email') has-error @enderror">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}">
                                    @error('email')
                                        <span class="help-block
                                        text-danger">{{ $message }}</span>
                                    @enderror   
                                </div>
                                <div class="form-group
                                @error('password') has-error @enderror">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control" id="password" name="password">
                                    @error('password')
                                        <span class="help-block
                                        text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            
                        </div>

                        
                        <div class="card-footer"> 
                            <button type="submit" class="btn btn-outline-yellow px-3">Login</button>
                            </form>
                        </div>
 
                        
                        <div class="hr-or"><span>or</span></div>
                        
                        <form action="{{ route('google.login') }}" method="GET">
                            @csrf
                            <button type="submit" class="btn btn-google">
                                <img src="https://www.gstatic.com/marketing-cms/assets/images/d5/dc/cfe9ce8b4425b410b49b7f2dd3f3/g.webp=s96-fcrop64=1,00000000ffffffff-rw"
                                    style="height:25px !important;"
                                    alt="Google Logo">
                                Sign in with Google
                            </button>
                        </form>

                        <form action="#" method="GET">
                            @csrf
                            <button type="submit" class="btn btn-facebook">
                                <i class="fa-brands fa-facebook-f px-1" ></i>
                                Sign in with Facebook
                            </button>
                        </form>

                        
                    </div>
                
                @endif
            </div><!--right col-md-4-->

        </div>

        <div class="row">
            <div class="col">
                <h3 class="text-center pt-5 pb-3">Pricing</h3>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3 col-sm-12"></div>

            <div class="col-md-2 col-sm-12 my-2">

                <div class="card form-bg unselectable "  >
                    <img src="..." class="hidden card-img-top" alt="...">
                    <div class="card-body ">
                        <h5 class="card-title text-center">No account</h5>
                        
                        <p><i class="fa fa-check" aria-hidden="true"></i>
                        Absolutely for free.</p> 

                        <p><i class="fa fa-check" aria-hidden="true"></i>
                        Unlimited link shortening.</p>

                        <p><i class="fa fa-check" aria-hidden="true"></i>
                        User account is not required.</p> 

                        <p><i class="fa fa-times" aria-hidden="true"></i>
                        No Customization of slug(link)</p>

                        <p><i class="fa fa-times" aria-hidden="true"></i>
                        No overview of Visitors</p>

                        <p><i class="fa fa-times" aria-hidden="true"></i>
                        No ownership of the slug(link)</p>
                        

                        <a href="#" class="btn btn-outline-yellow">Start</a>
                    </div>
                </div>

            </div>

            <div class="col-md-2 col-sm-12">

                <div class="card form-bg"  >
                    <img src="..." class="hidden card-img-top" alt="...">
                    <div class="card-body">
                        <h5 class="card-title">User account</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                        <a href="#" class="btn btn-primary">Go somewhere</a>
                    </div>
                </div>

            </div>

            <div class="col-md-2 col-sm-12">

                <div class="card form-bg"  >
                    <img src="..." class="hidden card-img-top" alt="...">
                    <div class="card-body">
                        <h5 class="card-title">Premium account</h5>
                        <p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
                        <a href="#" class="btn btn-primary">Go somewhere</a>
                    </div>
                </div>

            </div>

            

                   
            <div class="col-md-3 col-sm-12">

            </div>
             

        </div><!--row-->

    </div><!--container-fluid-->


    <script>

         //to be added at the footers (before the closing body tag)
        //this function copies the shortened URL to the clipboard
        function copyToClipboard(url) {
            const el = document.createElement('textarea');
            el.value = url;
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            alert('Copied to clipboard!');
        } 

        
        $(document).ready(function () {
    function isValidURL(url) {
        var pattern = /^(https?:\/\/)?(www\.)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(:\d+)?(\/[^\s]*)?$/;
        return pattern.test(url);
    }

    $("#url").on("input", function () {
        if (isValidURL($(this).val().trim())) {
            $(".check-icon").remove(); // Removes the checkmark permanently
        } else {
            $(".check-icon").fadeOut();
        }
    });
});

    </script>
     
    </body>


    
</html>
