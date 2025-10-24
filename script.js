// Mobile Menu Toggle
document.addEventListener('DOMContentLoaded', function() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const mobileMenu = document.querySelector('.mobile-menu');
    
    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            mobileMenu.classList.toggle('active');
            const icon = this.querySelector('svg');
            if (mobileMenu.classList.contains('active')) {
                icon.innerHTML = '<line x1="18" x2="6" y1="6" y2="18"/><line x1="6" x2="18" y1="6" y2="18"/>';
            } else {
                icon.innerHTML = '<line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>';
            }
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!mobileMenu.contains(e.target) && !mobileMenuBtn.contains(e.target)) {
                mobileMenu.classList.remove('active');
                const icon = mobileMenuBtn.querySelector('svg');
                icon.innerHTML = '<line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>';
            }
        });
        
        // Close menu when clicking a link
        mobileMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', function() {
                mobileMenu.classList.remove('active');
                const icon = mobileMenuBtn.querySelector('svg');
                icon.innerHTML = '<line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="18" y2="18"/>';
            });
        });
    }
});

// Smooth scroll for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            const navHeight = document.querySelector('.navbar').offsetHeight;
            const targetPosition = target.offsetTop - navHeight;
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    });
});

// Add scroll effect to navbar
let lastScroll = 0;
const navbar = document.querySelector('.navbar');

window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    
    if (currentScroll <= 0) {
        navbar.style.boxShadow = 'none';
    } else {
        navbar.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.3)';
    }
    
    lastScroll = currentScroll;
});

// Button click handlers (placeholder - يمكنك إضافة الوظائف الحقيقية هنا)
document.querySelectorAll('.btn-primary').forEach(button => {
    button.addEventListener('click', function() {
        window.location.href = 'payment.html';
    });
});

document.querySelectorAll('.btn-login').forEach(button => {
    button.addEventListener('click', function() {
        alert('سيتم توجيهك لصفحة تسجيل الدخول قريباً!');
    });
});

document.querySelectorAll('.btn-outline').forEach(button => {
    button.addEventListener('click', function() {
        const featuresSection = document.querySelector('#features');
        if (featuresSection) {
            const navHeight = document.querySelector('.navbar').offsetHeight;
            const targetPosition = featuresSection.offsetTop - navHeight;
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    });
});

// Add animation on scroll for feature cards
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver(function(entries) {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

// Observe all feature cards
document.querySelectorAll('.feature-card').forEach(card => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(20px)';
    card.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(card);
});

// Observe pricing card
const pricingCard = document.querySelector('.pricing-card');
if (pricingCard) {
    pricingCard.style.opacity = '0';
    pricingCard.style.transform = 'translateY(20px)';
    pricingCard.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
    observer.observe(pricingCard);
}

// Payment page functionality
function resetPaymentForm() {
    const paymentMethods = document.querySelector('.payment-methods');
    const paypalForm = document.getElementById('paypal-form');
    const instapayForm = document.getElementById('instapay-form');
    const paymentDescription = document.querySelector('.payment-description');
    
    if (paymentMethods) paymentMethods.style.display = 'grid';
    if (paypalForm) paypalForm.style.display = 'none';
    if (instapayForm) instapayForm.style.display = 'none';
    if (paymentDescription) paymentDescription.style.display = 'block';
    
    // Reset forms
    const paypalBookingForm = document.getElementById('paypal-booking-form');
    const instapayBookingForm = document.getElementById('instapay-booking-form');
    const paypalFileName = document.getElementById('paypal-file-name');
    const instapayFileName = document.getElementById('instapay-file-name');
    
    if (paypalBookingForm) paypalBookingForm.reset();
    if (instapayBookingForm) instapayBookingForm.reset();
    if (paypalFileName && currentLang == 'en') paypalFileName.textContent = 'No file chosen';
    if (paypalFileName && currentLang == 'ar') paypalFileName.textContent = 'لم يتم اختيار ملف';
    

    if (instapayFileName && currentLang == 'ar') instapayFileName.textContent = 'لم يتم اختيار ملف';
    if (instapayFileName && currentLang == 'en') instapayFileName.textContent = 'No file chosen';
}

// Payment method selection
const paypalCard = document.getElementById('paypal-card');
const instapayCard = document.getElementById('instapay-card');
const paypalForm = document.getElementById('paypal-form');
const instapayForm = document.getElementById('instapay-form');
const paymentMethods = document.querySelector('.payment-methods');


if (paypalCard) {
    paypalCard.addEventListener('click', () => {
        paymentMethods.style.display = 'none';
        paypalForm.style.display = 'block';
        document.querySelector('.payment-description').style.display = 'none';
    });
}

if (instapayCard) {
    instapayCard.addEventListener('click', () => {
        paymentMethods.style.display = 'none';
        instapayForm.style.display = 'block';
        document.querySelector('.payment-description').style.display = 'none';
    });
}

// File upload handling for PayPal
const paypalScreenshot = document.getElementById('paypal-screenshot');
const paypalFileName = document.getElementById('paypal-file-name');

if (paypalScreenshot) {
    paypalScreenshot.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            paypalFileName.textContent = e.target.files[0].name;
        } else {
            if (currentLang == 'ar') {
                paypalFileName.textContent = 'لم يتم اختيار ملف';
            }
            if (currentLang == 'en') {
                paypalFileName.textContent = 'No file chosen';
            } 
        }
    });
}

// File upload handling for InstaPay
const instapayScreenshot = document.getElementById('instapay-screenshot');
const instapayFileName = document.getElementById('instapay-file-name');

if (instapayScreenshot) {
    instapayScreenshot.addEventListener('change', (e) => {
        if (e.target.files.length > 0) {
            instapayFileName.textContent = e.target.files[0].name;
        } else {
            if (currentLang == 'ar') {
                instapayFileName.textContent = 'لم يتم اختيار ملف';
            }
            if (currentLang == 'en') {
                instapayFileName.textContent = 'No file chosen';
            }
        }
    });
}

// Form submission for PayPal
const paypalBookingForm = document.getElementById('paypal-booking-form');
if (paypalBookingForm) {
    paypalBookingForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        // Get selected payment type
        const paymentType = document.querySelector('input[name="paypal-type"]:checked').value;
        const amount = paymentType === 'friends' ? '$1.00' : '$1.57';
        
        // Hide form and show success message
        
        
        
    });
}

// Form submission for InstaPay
const instapayBookingForm = document.getElementById('instapay-booking-form');
if (instapayBookingForm) {
    instapayBookingForm.addEventListener('submit', (e) => {
        e.preventDefault();
        
        // Hide form and show success message
        
        
        
    });
}

// Inbox functionality
if (document.querySelector('.inbox-container')) {
    // Navigation items
    const navItems = document.querySelectorAll('.inbox-nav-item');
    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            navItems.forEach(nav => nav.classList.remove('active'));
            item.classList.add('active');
            
            // Here you would filter emails based on category
            const category = item.dataset.category;
            console.log('Switched to category:', category);
        });
    });

    // Email star toggle
    const starButtons = document.querySelectorAll('.email-star');
    starButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.stopPropagation();
            button.classList.toggle('starred');
        });
    });

    // Email item click
    const emailItems = document.querySelectorAll('.email-item');
    emailItems.forEach(item => {
        item.addEventListener('click', () => {
            item.classList.remove('unread');
            // Here you would open the email detail view
            console.log('Email opened');
        });
    });
}

// Language switcher functionality
let currentLang = 'en';

function getBrowserLanguage() {
    const browserLang = navigator.language || navigator.userLanguage;
    return browserLang.startsWith('ar') ? 'ar' : 'en';
}

function setLanguage(lang) {
    currentLang = lang;
    localStorage.setItem('preferredLang', lang);

    const htmlElement = document.documentElement;
    htmlElement.setAttribute('lang', lang);
    htmlElement.setAttribute('dir', lang === 'ar' ? 'rtl' : 'ltr');

    document.querySelectorAll('[data-i18n]').forEach(element => {
        const key = element.getAttribute('data-i18n');
        if (translations[lang] && translations[lang][key]) {
            if (element.tagName === 'INPUT' && element.type === 'text') {
                element.placeholder = translations[lang][key];
            } else {
                element.textContent = translations[lang][key];
            }
        }
    });

    updateLangButtons(lang);
}

function updateLangButtons(lang) {
    const enBtn = document.getElementById('lang-en');
    const arBtn = document.getElementById('lang-ar');
    const enBtnMobile = document.getElementById('lang-en-mobile');
    const arBtnMobile = document.getElementById('lang-ar-mobile');

    if (enBtn && arBtn) {
        if (lang === 'en') {
            enBtn.classList.add('active');
            arBtn.classList.remove('active');
        } else {
            arBtn.classList.add('active');
            enBtn.classList.remove('active');
        }
    }

    if (enBtnMobile && arBtnMobile) {
        if (lang === 'en') {
            enBtnMobile.classList.add('active');
            arBtnMobile.classList.remove('active');
        } else {
            arBtnMobile.classList.add('active');
            enBtnMobile.classList.remove('active');
        }
    }
}

function initLanguage() {
    const savedLang = localStorage.getItem('preferredLang');
    const initialLang = savedLang || getBrowserLanguage();
    setLanguage(initialLang);
}

document.addEventListener('DOMContentLoaded', function() {
    initLanguage();

    const enBtn = document.getElementById('lang-en');
    const arBtn = document.getElementById('lang-ar');
    const enBtnMobile = document.getElementById('lang-en-mobile');
    const arBtnMobile = document.getElementById('lang-ar-mobile');

    if (enBtn) {
        enBtn.addEventListener('click', () => setLanguage('en'));
    }

    if (arBtn) {
        arBtn.addEventListener('click', () => setLanguage('ar'));
    }

    if (enBtnMobile) {
        enBtnMobile.addEventListener('click', () => setLanguage('en'));
    }

    if (arBtnMobile) {
        arBtnMobile.addEventListener('click', () => setLanguage('ar'));
    }
});

console.log('VIP Mail - Ready to work! ✨');
