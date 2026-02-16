document.addEventListener('DOMContentLoaded', function() {
    
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.classList.add('fade');
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });
    
    const passwordInputs = document.querySelectorAll('input[type="password"]');
    passwordInputs.forEach(input => {
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
        
        const toggleBtn = document.createElement('button');
        toggleBtn.type = 'button';
        toggleBtn.innerHTML = '👁️';
        toggleBtn.style.cssText = `
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            z-index: 10;
        `;
        
        toggleBtn.addEventListener('click', function() {
            if(input.type === 'password') {
                input.type = 'text';
                toggleBtn.innerHTML = '🙈';
            } else {
                input.type = 'password';
                toggleBtn.innerHTML = '👁️';
            }
        });
        
        wrapper.appendChild(toggleBtn);
    });
    
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('change', function(e) {
            const files = e.target.files;
            if(files.length > 5) {
                alert('Vous ne pouvez sélectionner que 5 photos maximum');
                e.target.value = '';
                return;
            }
            
            Array.from(files).forEach(file => {
                if(file.size > 5 * 1024 * 1024) {
                    alert('La taille de chaque photo ne doit pas dépasser 5 Mo');
                    e.target.value = '';
                    return;
                }
            });
        });
    });
    
    const searchForm = document.querySelector('form[action="/liste-objets"]');
    if(searchForm) {
        const searchInput = searchForm.querySelector('input[name="search"]');
        const categorySelect = searchForm.querySelector('select[name="categorie"]');
        
        if(searchInput) {
            searchInput.addEventListener('input', debounce(function() {
                if(this.value.length > 2 || this.value.length === 0) {
                }
            }, 500));
        }
    }
    
    const priceInputs = document.querySelectorAll('input[type="number"]');
    priceInputs.forEach(input => {
        input.addEventListener('input', function() {
            if(this.value < 0) {
                this.value = 0;
            }
        });
    });
    
    const navLinks = document.querySelectorAll('.nav-link');
    const currentPath = window.location.pathname;
    navLinks.forEach(link => {
        if(link.getAttribute('href') === currentPath) {
            link.classList.add('active');
        }
    });
    
    const animateOnScroll = function() {
        const elements = document.querySelectorAll('.objet-card, .dashboard-card, .exchange-item');
        elements.forEach(el => {
            const rect = el.getBoundingClientRect();
            const isVisible = rect.top < window.innerHeight && rect.bottom > 0;
            
            if(isVisible) {
                el.style.opacity = '1';
                el.style.transform = 'translateY(0)';
            }
        });
    };
    
    window.addEventListener('scroll', debounce(animateOnScroll, 100));
    animateOnScroll();
    
    const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if(typeof bootstrap !== 'undefined') {
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });
    }
});

function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func.apply(this, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function formatPrice(price) {
    return new Intl.NumberFormat('fr-MG', {
        style: 'currency',
        currency: 'MGA',
        minimumFractionDigits: 0
    }).format(price);
}

function confirmAction(message) {
    return confirm(message);
}

const addForm = document.getElementById('addObjetForm');
if(addForm) {
    let isSubmitting = false;
    addForm.addEventListener('submit', function(e) {
        if(isSubmitting) {
            e.preventDefault();
            return false;
        }
        isSubmitting = true;
        const btn = this.querySelector('button[type="submit"]');
        if(btn) {
            btn.disabled = true;
            btn.innerHTML = '<span>⏳</span> Ajout en cours...';
        }
    });
}