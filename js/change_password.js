function validatePassword() {
    let newPass = document.getElementById("new_password").value;
    let confirmPass = document.getElementById("confirm_password").value;

    if (newPass.length < 6) {
        alert("Password must be at least 6 characters");
        return false;
    }

    if (newPass !== confirmPass) {
        alert("New password and confirm password do not match");
        return false;
    }
    return true;
}