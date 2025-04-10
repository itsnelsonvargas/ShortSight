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
    
    <div class="container-fluid">
 
        <form action="{{ route('checkSlug') }}" method="POST">
            @csrf
            @method('POST')
            <input type="text" name="slug" id="slug" placeholder="Enter slug">
            <button type="submit">Check Slug</button>
        </form>
        
    </div>
     
    </body>


    
</html>
