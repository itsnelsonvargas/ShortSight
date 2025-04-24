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

    <body  class="antialiased bg-white">
        <br>
    {!! QrCode::size(200)->generate($data) !!}
    </body>


    
</html>
