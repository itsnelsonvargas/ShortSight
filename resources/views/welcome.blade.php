<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <x:head></x:head>
    </head>
    <body class="antialiased">
    
    <div class="container-fluid">

        <div class="row">
            <div class="col-md-12">
             
            </div>
        </div>  


        <div class="row">

            <div class="col-md-4 col-sm-12"></div>

            <div class="col-md-4 col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Shorten a URL</h3> 


                        <!-- Display the message from the controller --> 
                        @if(isset($newSlug))
                            <div class="alert alert-success">
                                <p>You may now access the shortened link via: 
                                    <a href="{{ $submittedUrl }}"> 
                                        <strong>{{ env('APP_URL') . '/' . $newSlug  }}</strong>
                                    </a>
                                </p>
                                  
                                <button class="btn btn-primary" onclick="copyToClipboard('{{ env('APP_URL') . '/' . $newSlug }}')"> 
                                     <i class="fa fa-copy"></i> Copy
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
                            <button type="submit" class="btn btn-primary">Shorten</button>
                        </form>
                    </div>

                </div><!--card-->

            </div>
            <div class="col-md-4 col-sm-12"></div>

        </div>

        <div class="row">
            <div class="col-md-4 col-sm-12"></div>

            <div class="col-md-4 col-sm-12">

                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Login</h3>
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
                        <button type="submit" class="btn btn-primary">Login</button>
                    </div>
                </div>

            </div>

                   
            <div class="col-md-4 col-sm-12"></div>
             

        </div><!--row-->

    </div><!--container-fluid-->
    
     
    </body>
</html>
