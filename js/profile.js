document.addEventListener("DOMContentLoaded", function () {
    const input = document.getElementById("profile_image");
    const preview = document.querySelector(".profile-preview");

    if (!input || !preview) return;

    input.addEventListener("change", function () {
        const file = this.files[0];
        if (!file) return;

        if (!file.type.startsWith("image/")) {
            alert("Please select an image file");
            this.value = "";
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
        };
        reader.readAsDataURL(file);
    });
});
