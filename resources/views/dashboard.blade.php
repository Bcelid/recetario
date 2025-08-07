@include('layouts.header')
@include('layouts.sidebar')
<div class="main-content">
    <h1>Bienvenido, {{ auth()->user()->name }}</h1>
    <p>Este es el dashboard accesible para todos los roles.</p>
</div>
@include('layouts.footer')
</body>

</html>
