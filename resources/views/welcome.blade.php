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
                        </form>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-outline-yellow px-3">Login</button>
                    </div>
                </div>
                
            </div><!--right col-md-4-->

        </div>

        <div class="row">
            <div class="col-md-4 col-sm-12"></div>

            <div class="col-md-4 col-sm-12">

               

            </div>

                   
            <div class="col-md-4 col-sm-12">

            </div>
             

        </div><!--row-->

    </div><!--container-fluid-->
    
     
    </body>
</html>
