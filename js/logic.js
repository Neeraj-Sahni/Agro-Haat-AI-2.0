function validateForm() {
    let pwd = document.getElementById("password").value;
    let cpwd = document.getElementById("cpassword").value;

    if (pwd !== cpwd) {
        alert("Passwords do not match!");
        return false;
    }

    let phone = document.querySelector('input[name="phone"]');
    if (phone && !/^[0-9]{10}$/.test(phone.value)) {
        alert("Phone number must be exactly 10 digits!");
        return false;
    }

    return true;
}

document.addEventListener('DOMContentLoaded', function () {

    // ========== FORGOT PASSWORD MODAL ==========
    const forgotLink = document.getElementById('showForgot');
    const forgotBox  = document.getElementById('forgotBox');
    const closeBtn   = document.querySelector('.close');

    if (forgotLink && forgotBox) {
        forgotLink.addEventListener('click', function (e) {
            e.preventDefault();
            forgotBox.style.display = 'block';
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function () {
            forgotBox.style.display = 'none';
        });
    }

    window.addEventListener('click', function (e) {
        if (forgotBox && e.target === forgotBox) {
            forgotBox.style.display = 'none';
        }
    });

    // ========== FORGOT FORM SUBMIT ==========
    const forgotForm = document.getElementById('forgotForm');
    if (forgotForm) {
        forgotForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            const email     = this.email.value.trim();
            const resultDiv = document.getElementById('resetResult');
            const submitBtn = this.querySelector('button[type="submit"]');

            if (!email) {
                resultDiv.innerHTML = '<span style="color:red;">Please enter your email.</span>';
                return;
            }

            // Loading state
            resultDiv.innerHTML  = '⏳ Sending OTP...';
            submitBtn.disabled   = true;
            submitBtn.innerText  = 'Sending...';

            try {
                const formData = new FormData();
                formData.append('email', email);

                const response = await fetch('actions/forgot_password_action.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    // OTP dikhao testing ke liye
                    let otpMsg = data.debug_otp
                        ? `<br><small style="color:#666;">Testing OTP: <b>${data.debug_otp}</b></small>`
                        : '';

                    resultDiv.innerHTML = `<span style="color:green;">✅ OTP sent! Check your email.${otpMsg}</span>`;

                    // 3 second baad reset password page pe jao
                    setTimeout(() => {
                        forgotBox.style.display = 'none';
                        window.location.href    = 'reset_password.php';
                    }, 3000);

                } else {
                    resultDiv.innerHTML = `<span style="color:red;">❌ ${data.message || 'Failed to send OTP'}</span>`;
                    submitBtn.disabled  = false;
                    submitBtn.innerText = 'Send OTP';
                }

            } catch (error) {
                resultDiv.innerHTML = '<span style="color:red;">❌ Network error. Please try again.</span>';
                submitBtn.disabled  = false;
                submitBtn.innerText = 'Send OTP';
                console.error('Error:', error);
            }
        });
    }

    // ========== PHONE NUMBER - ONLY DIGITS ==========
    const phoneInput = document.querySelector('input[name="phone"]');
    if (phoneInput) {
        phoneInput.addEventListener('input', function () {
            this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10);
        });
    }
});