function validateForm() {
    let pwd = document.getElementById("password").value;
    let cpwd = document.getElementById("cpassword").value;

    if (pwd !== cpwd) {
        alert("Password not matched");
        return false;
    }
    return true;
}

document.addEventListener('DOMContentLoaded', function() {
    const forgotLink = document.getElementById('showForgot');
    const forgotBox = document.getElementById('forgotBox');
    const closeBtn = document.querySelector('.close');
    
    // Show modal when clicking "Forgot Password?"
    forgotLink.addEventListener('click', function(e) {
        e.preventDefault();
        forgotBox.style.display = 'block';
    });
    
    // Close modal when clicking X
    closeBtn.addEventListener('click', function() {
        forgotBox.style.display = 'none';
    });
    
    // Close modal when clicking outside
    window.addEventListener('click', function(e) {
        if (e.target === forgotBox) {
            forgotBox.style.display = 'none';
        }
    });
    
    // Step 1: Send OTP Form Handler
    const forgotForm = document.getElementById('forgotForm');
    if (forgotForm) {
        forgotForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('forgotEmail').value;
            const resultDiv = document.getElementById('resetResult');
            const sendBtn = document.getElementById('sendOtpBtn');
            
            sendBtn.disabled = true;
            sendBtn.innerText = 'Generating OTP...';
            resultDiv.innerHTML = '<span style="color:#2e7d32;">Generating OTP, please wait...</span>';
            
            try {
                const response = await fetch('/actions/forgot_password_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=request&email=${encodeURIComponent(email)}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    if (data.email_sent) {
                        resultDiv.innerHTML = `<div style="background:#e8f5e9; border:1px solid #a5d6a7; color:#2e7d32; padding:10px; border-radius:8px;">
                            📩 <strong>OTP Sent to Email Inbox!</strong>
                            <br><small style="color:#555;">Check your email inbox for the 6-digit OTP code and enter it below.</small>
                        </div>`;
                    } else {
                        resultDiv.innerHTML = `<div style="background:#e8f5e9; border:1px solid #a5d6a7; color:#2e7d32; padding:10px; border-radius:8px;">
                            🔑 <strong>Verification OTP Code:</strong> <span style="font-size:18px; letter-spacing:2px; font-weight:bold;">${data.otp}</span>
                            <br><small style="color:#555;">Enter this 6-digit code below to set new password.</small>
                        </div>`;
                    }
                    
                    document.getElementById('resetEmail').value = email;
                    forgotForm.style.display = 'none';
                    document.getElementById('resetForm').style.display = 'block';
                } else {
                    resultDiv.innerHTML = `<span style="color:#d32f2f;">❌ ${data.message || 'Email not found!'}</span>`;
                }
            } catch (error) {
                resultDiv.innerHTML = '<span style="color:#d32f2f;">Network error. Please try again.</span>';
            }
            
            sendBtn.disabled = false;
            sendBtn.innerText = 'Send Verification OTP';
        });
    }

    // Step 2: Reset Password Form Handler
    const resetForm = document.getElementById('resetForm');
    if (resetForm) {
        resetForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const email = document.getElementById('resetEmail').value;
            const otp = document.getElementById('otpCode').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const resultDiv = document.getElementById('resetResult');
            const resetBtn = document.getElementById('resetPassBtn');

            if (newPassword !== confirmPassword) {
                resultDiv.innerHTML = '<span style="color:#d32f2f;">❌ New password and confirm password do not match!</span>';
                return;
            }

            resetBtn.disabled = true;
            resetBtn.innerText = 'Resetting Password...';

            try {
                const response = await fetch('/actions/forgot_password_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=reset&email=${encodeURIComponent(email)}&otp=${encodeURIComponent(otp)}&new_password=${encodeURIComponent(newPassword)}`
                });

                const data = await response.json();

                if (data.success) {
                    resultDiv.innerHTML = `<div style="background:#e8f5e9; color:#2e7d32; padding:10px; border-radius:8px;">
                        ✅ ${data.message}
                    </div>`;
                    resetForm.style.display = 'none';
                    setTimeout(() => {
                        window.location.href = '/login?msg=Password+reset+successful!+Please+login.&msg_type=success';
                    }, 2000);
                } else {
                    resultDiv.innerHTML = `<span style="color:#d32f2f;">❌ ${data.message}</span>`;
                }
            } catch (err) {
                resultDiv.innerHTML = '<span style="color:#d32f2f;">Network error. Failed to reset password.</span>';
            }

            resetBtn.disabled = false;
            resetBtn.innerText = 'Reset & Save Password';
        });
    }
});