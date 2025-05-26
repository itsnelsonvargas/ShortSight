 <!DOCTYPE html>
 <html lang="en">
 <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
 </head>
 <body>
    <form action="{{ route('saveUserAccount') }}" method="POST">
        @csrf
        @method('POST')
        <div>
            <label for="url">Enter URL:</label>
            <input type="url" id="url" name="url" required>
        </div>
        <div>
            <button type="submit">Shorten URL</button>
        </div>
    </form> 
 </body>
 </html>


 