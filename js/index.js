// Initialize EmailJS
(function() {
    emailjs.init("IM6lTrnSiOMJExFS-"); // Replace with your actual public key
})();

// Navigation
const hamburger = document.querySelector(".hamburger");
const navlist = document.querySelector(".nav-list");

if (hamburger) {
    hamburger.addEventListener("click", () => {
        navlist.classList.toggle("open");
    });
}

// Popup
const popup = document.querySelector(".popup");
const closePopup = document.querySelector(".popup-close");

if (popup) {
    closePopup.addEventListener("click", () => {
        popup.classList.add("hide-popup");
    });

    window.addEventListener("load", () => {
        setTimeout(() => {
            popup.classList.remove("hide-popup");
        }, 1000);
    });
}

// Contact Form Helper Functions
function showAlert(message, type) {
    const alertMessage = document.getElementById('alert-message');
    if (alertMessage) {
        alertMessage.className = `alert alert-${type}`;
        alertMessage.style.display = 'block';
        alertMessage.textContent = message;

        // Auto-hide alert after 5 seconds
        setTimeout(() => {
            alertMessage.style.display = 'none';
        }, 5000);
    }
}

function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Contact Form Submit Handler
function sendEmail(e) {
    e.preventDefault();

    const emailInput = document.getElementById('email');
    const subjectInput = document.getElementById('subject');
    const messageInput = document.getElementById('message');
    const submitButton = e.target.querySelector('button[type="submit"]');

    // Validate inputs
    if (!validateEmail(emailInput.value)) {
        showAlert('Please enter a valid email address', 'danger');
        emailInput.focus();
        return false;
    }

    if (subjectInput.value.length < 2) {
        showAlert('Please enter a valid subject', 'danger');
        subjectInput.focus();
        return false;
    }

    if (messageInput.value.length < 10) {
        showAlert('Message must be at least 10 characters long', 'danger');
        messageInput.focus();
        return false;
    }

    // Show loading state
    submitButton.disabled = true;
    submitButton.innerHTML = '<i class="bx bx-loader-alt bx-spin"></i> Sending...';

    // Prepare template parameters
    const templateParams = {
        email: emailInput.value,
        subject: subjectInput.value,
        message: messageInput.value
    };

    // Send email
    emailjs.send('service_hei64rq', 'YOUR_TEMPLATE_ID', templateParams)
        .then(function(response) {
            showAlert('Thank you for your message. We will get back to you soon!', 'success');
            document.getElementById('contact-form').reset();
        }, function(error) {
            showAlert('Oops! Something went wrong. Please try again later.', 'danger');
            console.error('EmailJS Error:', error);
        })
        .finally(function() {
            // Reset button state
            submitButton.disabled = false;
            submitButton.innerHTML = 'Send Message';
        });

    return false;
}

// Add event listeners when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Contact form submit listener
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', sendEmail);
    }

    // Input validation listeners
    const emailInput = document.getElementById('email');
    const subjectInput = document.getElementById('subject');
    const messageInput = document.getElementById('message');

    if (emailInput) {
        emailInput.addEventListener('blur', function() {
            if (!validateEmail(this.value) && this.value !== '') {
                this.classList.add('error');
                showAlert('Please enter a valid email address', 'danger');
            } else {
                this.classList.remove('error');
            }
        });
    }

    if (subjectInput) {
        subjectInput.addEventListener('blur', function() {
            if (this.value.length < 2 && this.value !== '') {
                this.classList.add('error');
                showAlert('Subject is too short', 'danger');
            } else {
                this.classList.remove('error');
            }
        });
    }

    if (messageInput) {
        messageInput.addEventListener('blur', function() {
            if (this.value.length < 10 && this.value !== '') {
                this.classList.add('error');
                showAlert('Message must be at least 10 characters long', 'danger');
            } else {
                this.classList.remove('error');
            }
        });
    }
});