<div>
    <!-- He who is contented is rich. - Laozi -->
</div>

<form action="{{ route('shorten.link') }}" method="POST">
    @csrf
    <div>
        <label for="url">Enter URL:</label>
        <input type="url" id="url" name="url" required>
    </div>
    <div>
        <button type="submit">Shorten URL</button>
    </div>
</form>
