<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

    <head>

        <x:head></x:head>

        <script>
            const checkSlugUrl = '{{ route("checkSlug") }}';
        </script>

        <meta name="csrf-token" content="{{ csrf_token() }}">

        <script src="{{ asset('js/home.js') }}"></script>

    </head>

    <body class="antialiased">

    <nav class="navbar w-100 navbar-expand-lg navbar-light bg-light px-4">
        <a class="navbar-brand" href="{{ route('home') }}">ShortSight</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav"
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto gap-3">
                <li class="nav-item active">
                    <a class="nav-link" href="#">Home <span class="sr-only">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Features</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Pricing</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Contact</a>
                </li>
            </ul>
        </div>
    </nav>



    <div class="container-fluid">


        <div class="row">
            <div class="col h-25">

                <h1 class="text-center p-5">{{ env('APP_NAME') }}</h1>

            </div>
        </div>



        <div class="row">

            <div class="col-md-4 col-sm-12">

            </div><!--left col-md-4-->

            <div class="col-md-4 col-sm-12">

                <div class="card form-bg">

                    <div class="card-header">

                        <h3 class="text-center">Shorten a URL<i class="fa fa-link" aria-hidden="true"></i></h3>


                        <!-- Display the message from the controller -->
                        @if(isset($data['newSlug']))
   
                            <div class="alert alert-light text-center">

                        
                                <p>You may now access the shortened link via:
                                    <a href="{{ url($data['submittedUrl']) }}" target="_blank">
                                        <strong>{{ url($data['newSlug']) }}</strong>
                                    </a>
                                    <button class="btn  ss" onclick="copyToClipboard('{{ env('APP_URL') . '/' . $data['newSlug'] }}')"
                                        style="background-color:transparent; border:none;">
                                        <i class="fa fa-copy"></i> Copy
                                    </button>
                                </p>

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



                                <input type="text" class="form-control" id="url" name="url" required>
                                @error('url')
                                    <span class="help-block
                                    text-danger">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group">

                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="customSlug" name="customSlug" value="1"
                                        @if(Auth::check())
                                          checked
                                        @endif>
                                    <label class="form-check-label" for="customSlug">Use custom slug</label>
                                </div>

                                <small id="slug-status"></small>


                                <input type="text" class="form-control mt-2" id="customSlugInput" name="customSlugInput"
                                    @if(Auth::check())
                                        placeholder="Enter custom slug"
                                        style="display:block;">
                                    @else
                                        placeholder="For signed-in users only" disabled
                                        style="display:none;">
                                    @endif

                            </div>

                            <div class="form-group mt-3" id="formatOptions"
                                @if(Auth::check())
                                    style="display:none;">
                                @else
                                    style="display:block;">
                                @endif
                                <label for="format">Slug Format:</label>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" id="random7" name="format" value="random7" checked>
                                    <label class="form-check-label" for="random7">
                                        Random 7 characters ( <span   class="text-muted">Ex: {{ env('APP_URL') }}/A1B2C3D</span>)
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" id="random6Hyphen" name="format" value="random6Hyphen">
                                    <label class="form-check-label" for="random6Hyphen">
                                        Random 6 characters with hyphen( <span   class="text-muted">Ex: {{ env('APP_URL') }}/ABC-123</span>)
                                    </label>
                                </div>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const customSlugCheckbox = document.getElementById('customSlug');
                                    const customSlugInput = document.getElementById('customSlugInput');
                                    const formatOptions = document.getElementById('formatOptions');

                                    customSlugCheckbox.addEventListener('change', function () {

                                        // Handle the toggle for using a custom slug
                                        if (this.checked) {
                                            // If the user is authenticated
                                            @if(Auth::check())
                                                // Enable the custom slug input
                                                customSlugInput.disabled = false;
                                            @else
                                                // Otherwise, disable it
                                                customSlugInput.disabled = true;
                                            @endif

                                            // Show the custom slug input field
                                            customSlugInput.style.display = "block";

                                            // Hide the format options
                                            formatOptions.style.display = "none";
                                        } else {
                                            // Always disable the input when checkbox is unchecked
                                            customSlugInput.disabled = true;

                                            // Hide the custom slug input field
                                            customSlugInput.style.display = "none";

                                            // Show the format options
                                            formatOptions.style.display = "block";
                                        }

                                    });
                                });
                            </script>
                    </div>

                    <div class="card-footer">
                            <button type="submit" id="submitButton" class="btn btn-outline-yellow  ">Shorten</button>
                        </form>
                    </div>

                </div><!--card-->

            </div><!--center col-md-4-->

            <div class="col-md-2 col-sm-12">
            </div>

            <div class="col-md-2 col-sm-12">
                @if(Auth::check())
                    <p>Welcome, {{ Auth::user()->name }}!</p>
                    <small class="text-muted">You are logged in using {{ Auth::user()->email; }}</small>
                    <hr>
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
                            <small>To track and manage your shorten link. </small>
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
                            <button type="submit" class="btn btn-outline-yellow px-3" style="width:100%;">Login</button>
                            </form>
                        </div>
                        <div class="card-footer">
                            <button type="submit" class="btn btn-outline-yellow px-3" style="width:100%;">Login</button>
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

                        <form action="{{ route('facebook.login') }}" method="GET">
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



     <footer>
        <div class="container">
            <div class="row">
                <div class="col">
                    <h6 class="text-center">Footer</h6>
                </div>
            </div>
            <div class="row">
                <div class="col-md col-sm-12">
                    <h4 class="text-center">About us</h4>
                </div>
                <div class="col-md col-sm-12">
                    <h4 class="text-center">Contacts</h4>
                </div>
                <div class="col-md col-sm-12">
                    <h4 class="text-center">About us</h4>
                </div>
                <div class="col-md col-sm-12">
                    <h4 class="text-center">Comment and suggestions</h4>
                </div>
            </div>
        </div>
    </footer>
    </body>



</html>
