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
    
    // Handle form submission
    document.getElementById('forgotForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const email = this.email.value;
        const resultDiv = document.getElementById('resetResult');
        
        // Show loading state
        resultDiv.innerHTML = 'Sending OTP...';
        
        try {
            const formData = new URLSearchParams();
            formData.append('email', email);

            const response = await fetch('actions/forgot_password_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: formData.toString()
            });
            
            const data = await response.json();
            
            if (response.ok) {
                resultDiv.innerHTML = '✅ OTP sent successfully! Check your email.';
                this.reset(); // Clear the form
                
                // Optional: Close modal after 3 seconds
                setTimeout(() => {
                    forgotBox.style.display = 'none';
                    resultDiv.innerHTML = '';
                }, 3000);
            } else {
                resultDiv.innerHTML = `${data.message || 'Failed to send OTP'}`;
            }
        } catch (error) {
            resultDiv.innerHTML = 'Network error. Please try again.';
            console.error('Error:', error);
        }
    });
});