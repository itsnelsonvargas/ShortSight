<div>
    <!-- He who is contented is rich. - Laozi -->
</div>

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

asdasds
