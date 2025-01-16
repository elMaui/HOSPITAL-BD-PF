const btnSignIn = document.getElementById("sign-in");
const btnSignUp = document.getElementById("sign-up");
const containerformRegister = document.querySelector(".register");
const containerformLogin = document.querySelector(".login");

if (btnSignIn && btnSignUp && containerformRegister && containerformLogin) {
    btnSignIn.addEventListener("click", e => {
        e.preventDefault(); 
        containerformRegister.classList.add("hide");
        containerformLogin.classList.remove("hide");
    });

    btnSignUp.addEventListener("click", e => {
        e.preventDefault(); 
        containerformLogin.classList.add("hide");
        containerformRegister.classList.remove("hide");
    });
} else {
    console.error("Uno o más elementos del DOM no se encontraron.");
}
  