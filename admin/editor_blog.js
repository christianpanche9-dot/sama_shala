(function () {
var contenedorEditor = document.querySelector('#editor-contenido');
var campoContenido = document.querySelector('#contenido');
var formulario = document.querySelector('#formulario-blog');
if (!contenedorEditor || !campoContenido || !formulario) {
return;
}

var quill = new Quill('#editor-contenido', {
theme: 'snow',
modules: {
toolbar: {
container: [
[{ header: [2, 3, false] }],
['bold', 'italic', 'underline', 'strike'],
['blockquote'],
[{ list: 'ordered' }, { list: 'bullet' }],
['link', 'image'],
['clean']
],
handlers: {
image: subirImagen
}
}
}
});

if (campoContenido.value) {
quill.root.innerHTML = campoContenido.value;
}

function subirImagen() {
var entrada = document.createElement('input');
entrada.setAttribute('type', 'file');
entrada.setAttribute('accept', 'image/jpeg,image/png,image/webp');
entrada.click();
entrada.addEventListener('change', function () {
var archivo = entrada.files[0];
if (!archivo) {
return;
}
var rango = quill.getSelection(true);
var datos = new FormData();
datos.append('archivo', archivo);
fetch('subir_imagen_blog.php', {
method: 'POST',
body: datos
})
.then(function (respuesta) {
return respuesta.json();
})
.then(function (resultado) {
if (resultado && resultado.ok && resultado.url) {
quill.insertEmbed(rango.index, 'image', resultado.url, 'user');
quill.setSelection(rango.index + 1);
} else {
alert(resultado && resultado.error ? resultado.error : 'No se ha podido subir la imagen.');
}
})
.catch(function () {
alert('No se ha podido subir la imagen.');
});
});
}

formulario.addEventListener('submit', function () {
campoContenido.value = quill.root.innerHTML;
});
})();
