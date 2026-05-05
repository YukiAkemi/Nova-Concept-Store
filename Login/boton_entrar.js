document.getElementById("submitBtn").addEventListener("click", function() {
    const usuario = document.getElementById("usuario").value;
    const contrasena = document.getElementById("contrasena").value;
    const message = document.getElementById("message");

    if (usuario === "" || contrasena === "") {
        message.innerText = "Por favor, llene todos los campos.";
        message.style.color = "red";
        return;
    }
    // FORM DATA para enviar al PHP
    const formData = new FormData();
    formData.append("usuario", usuario);
    formData.append("contrasena", contrasena);
    // IMPORTANTE: La ruta ahora es 'auth/login.php' porque 
    // index.html está en la raíz y busca dentro de la carpeta auth.
   // El fetch debe apuntar a la carpeta Login porque ahí está el PHP
    fetch("Login/login.php", { 
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            message.innerText = "¡Conexión exitosa! Redirigiendo...";
            message.style.color = "green";
            
            // Aquí decides a dónde enviarlo según su cargo
            // Ejemplo:
            window.location.href = "privera_vista.html"; 
        } else {
            message.innerText = data.message;
            message.style.color = "red";
        }
    })
    .catch(error => {
        console.error("Error:", error);
        message.innerText = "Error de conexión al intentar loguear.";
        message.style.color = "red";
    });
});