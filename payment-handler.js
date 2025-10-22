// Payment Form Handler with hCaptcha Integration

// Show error message
function showError(message) {
    const errorDiv = document.getElementById('error-message');
    const errorText = document.getElementById('error-message-text');
    errorText.textContent = message;
    errorDiv.style.display = 'block';
    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    
    // Hide error after 5 seconds
    setTimeout(() => {
        errorDiv.style.display = 'none';
    }, 5000);
}

// Hide error message
function hideError() {
    const errorDiv = document.getElementById('error-message');
    errorDiv.style.display = 'none';
}

// Validate form data
function validateForm(formData) {
    const email = formData.get('email');
    const desiredEmail = formData.get('desired_email');
    const phone = formData.get('phone');
    const screenshot = formData.get('screenshot');
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email || !emailRegex.test(email)) {
        showError(currentLang === 'ar' ? 'البريد الإلكتروني غير صالح' : 'Invalid email address');
        return false;
    }
    
    // Desired email validation
    const desiredEmailRegex = /^[a-z0-9._-]+$/;
    if (!desiredEmail || !desiredEmailRegex.test(desiredEmail)) {
        showError(currentLang === 'ar' ? 'عنوان البريد المطلوب غير صالح. استخدم حروف صغيرة وأرقام ونقاط وشرطات فقط' : 'Invalid desired email. Use only lowercase letters, numbers, dots, underscores and hyphens');
        return false;
    }
    
    // Check minimum length
    if (desiredEmail.length < 3) {
        showError(currentLang === 'ar' ? 'عنوان البريد يجب أن يكون 3 أحرف على الأقل' : 'Email address must be at least 3 characters');
        return false;
    }
    
    // Check maximum length
    if (desiredEmail.length > 64) {
        showError(currentLang === 'ar' ? 'عنوان البريد طويل جداً (الحد الأقصى 64 حرف)' : 'Email address too long (max 64 characters)');
        return false;
    }
    
    // Check for consecutive dots
    if (desiredEmail.includes('..')) {
        showError(currentLang === 'ar' ? 'لا يمكن استخدام نقطتين متتاليتين' : 'Consecutive dots are not allowed');
        return false;
    }
    
    // Check start/end with dot or hyphen
    if (/^[.-]|[.-]$/.test(desiredEmail)) {
        showError(currentLang === 'ar' ? 'لا يمكن أن يبدأ أو ينتهي البريد بنقطة أو شرطة' : 'Email cannot start or end with dot or hyphen');
        return false;
    }
    
    // Phone validation (basic)
    if (!phone || phone.length < 10) {
        showError(currentLang === 'ar' ? 'رقم الهاتف غير صالح' : 'Invalid phone number');
        return false;
    }
    
    // Screenshot validation
    if (!screenshot || screenshot.size === 0) {
        showError(currentLang === 'ar' ? 'يرجى رفع صورة التحويل' : 'Please upload payment screenshot');
        return false;
    }
    
    // File size validation (max 5MB)
    if (screenshot.size > 5 * 1024 * 1024) {
        showError(currentLang === 'ar' ? 'حجم الصورة كبير جداً (الحد الأقصى 5 ميجابايت)' : 'Image size too large (max 5MB)');
        return false;
    }
    
    // File type validation
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(screenshot.type)) {
        showError(currentLang === 'ar' ? 'نوع الملف غير مدعوم. يرجى رفع صورة' : 'Unsupported file type. Please upload an image');
        return false;
    }
    
    return true;
}

// Handle form submission
async function handleFormSubmit(event, formId) {
    event.preventDefault();
    hideError();
    
    const form = event.target;
    const submitBtn = form.querySelector('.btn-submit');
    const originalBtnText = submitBtn.textContent;
    
    // Get hCaptcha response
    const hcaptchaResponse = form.querySelector('.h-captcha textarea')?.value;
    
    if (!hcaptchaResponse) {
        showError(currentLang === 'ar' ? 'يرجى التحقق من أنك لست روبوت' : 'Please complete the captcha verification');
        return;
    }
    
    // Create FormData object
    const formData = new FormData(form);
    formData.append('h-captcha-response', hcaptchaResponse);
    
    // Validate form data
    if (!validateForm(formData)) {
        return;
    }
    
    // Disable submit button
    submitBtn.disabled = true;
    submitBtn.textContent = currentLang === 'ar' ? 'جاري الإرسال...' : 'Submitting...';
    submitBtn.style.opacity = '0.6';
    submitBtn.style.cursor = 'not-allowed';
    
    try {
        // Send data to API
        const response = await fetch('api/apiv1.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Hide payment form
            document.getElementById(formId).style.display = 'none';
            
            // Show success message
            const successMessage = document.getElementById('success-message');
            successMessage.style.display = 'block';
            successMessage.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
            // Reset form
            form.reset();
            if (typeof hcaptcha !== 'undefined') {
                hcaptcha.reset();
            }
        } else {
            // Show error message
            showError(result.message || (currentLang === 'ar' ? 'حدث خطأ. يرجى المحاولة مرة أخرى.' : 'An error occurred. Please try again.'));
            
            // Re-enable submit button
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
            
            // Reset hCaptcha
            if (typeof hcaptcha !== 'undefined') {
                hcaptcha.reset();
            }
        }
    } catch (error) {
        console.error('Error:', error);
        showError(currentLang === 'ar' ? 'حدث خطأ في الاتصال. يرجى التحقق من الإنترنت والمحاولة مرة أخرى.' : 'Connection error. Please check your internet and try again.');
        
        // Re-enable submit button
        submitBtn.disabled = false;
        submitBtn.textContent = originalBtnText;
        submitBtn.style.opacity = '1';
        submitBtn.style.cursor = 'pointer';
        
        // Reset hCaptcha
        if (typeof hcaptcha !== 'undefined') {
            hcaptcha.reset();
        }
    }
}

// Initialize form handlers when page loads
document.addEventListener('DOMContentLoaded', function() {
    // PayPal form handler
    const paypalForm = document.getElementById('paypal-booking-form');
    if (paypalForm) {
        paypalForm.addEventListener('submit', function(e) {
            handleFormSubmit(e, 'paypal-form');
        });
    }
    
    // InstaPay form handler
    const instapayForm = document.getElementById('instapay-booking-form');
    if (instapayForm) {
        instapayForm.addEventListener('submit', function(e) {
            handleFormSubmit(e, 'instapay-form');
        });
    }
});
