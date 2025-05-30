// ===============================
// VALIDATION JS GLOBALE MOVIESHOP
// ===============================

// --- Lettres uniquement (nom/prénom) ---
function onlyLetters(e) {
    const char = String.fromCharCode(e.which);
    if (!/[a-zA-ZÀ-ÿ\s-]/.test(char)) {
        e.preventDefault();
    }
}

document.querySelectorAll('input[name="nom"], input[name="prenom"]').forEach(input => {
    input.addEventListener('keypress', onlyLetters);
});

// --- Numéro de téléphone malgache ---
// 10 chiffres, commence par 033/034/038/032/037
function validatePhoneInput(input) {
    let alreadyAlerted = false;
    input.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
        if (this.value.length > 10) this.value = this.value.slice(0, 10);
        alreadyAlerted = false; // reset flag on input
    });
    input.addEventListener('blur', function() {
        if (!/^(033|034|038|032|037)[0-9]{7}$/.test(this.value)) {
            if (!alreadyAlerted) {
                alreadyAlerted = true;
                alert('Le numéro doit commencer par 033, 034, 038, 032 ou 037 et contenir 10 chiffres.');
                // Ne pas relancer le focus pour éviter la boucle
            }
        } else {
            alreadyAlerted = false;
        }
    });
}
document.querySelectorAll('input[name="telephone"]').forEach(validatePhoneInput);

// --- Alphanumérique (adresse, ville, etc.) ---
function onlyAlphaNum(e) {
    const char = String.fromCharCode(e.which);
    if (!/[a-zA-Z0-9À-ÿ\s,\.\-]/.test(char)) {
        e.preventDefault();
    }
}
document.querySelectorAll('input[name="adresse"], input[name="ville"]').forEach(input => {
    input.addEventListener('keypress', onlyAlphaNum);
});

// --- Titre de film (lettres, chiffres, espaces, ponctuation simple) ---
function onlyTitleChars(e) {
    const char = String.fromCharCode(e.which);
    if (!/[a-zA-Z0-9À-ÿ\s,\.\-\!\?\'":]/.test(char)) {
        e.preventDefault();
    }
}
document.querySelectorAll('input[name="titre"]').forEach(input => {
    input.addEventListener('keypress', onlyTitleChars);
});

// --- Barre de recherche (alphanumérique + espaces) ---
function onlySearchChars(e) {
    const char = String.fromCharCode(e.which);
    if (!/[a-zA-Z0-9À-ÿ\s]/.test(char)) {
        e.preventDefault();
    }
}
document.querySelectorAll('input[name="search"]').forEach(input => {
    input.addEventListener('keypress', onlySearchChars);
});

// --- Email (complément HTML5) ---
document.querySelectorAll('input[type="email"]').forEach(input => {
    input.addEventListener('blur', function() {
        if (this.value && !this.value.match(/^[^@\s]+@[^@\s]+\.[^@\s]+$/)) {
            alert('Email invalide.');
            this.focus();
        }
    });
});

// --- Mot de passe (min 8 caractères, 1 lettre, 1 chiffre) ---
const pwdInput = document.querySelector('input[name="password"]');
if (pwdInput) {
    pwdInput.addEventListener('blur', function() {
        if (!/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/.test(this.value)) {
            alert('Le mot de passe doit contenir au moins 8 caractères, dont une lettre et un chiffre.');
            this.focus();
        }
    });
}

// --- Numérique pur (quantité, prix) ---
document.querySelectorAll('input[name="quantite"], input[name="prix"]').forEach(input => {
    input.addEventListener('input', function() {
        this.value = this.value.replace(/[^0-9]/g, '');
    });
});

// --- Fonction globale pour appliquer la validation sur tous les inputs (utile pour les modals dynamiques) ---
function applyValidation() {
    // Lettres uniquement (nom/prénom)
    document.querySelectorAll('input[name="nom"], input[name="prenom"]').forEach(input => {
        input.removeEventListener('keypress', onlyLetters);
        input.addEventListener('keypress', onlyLetters);
    });
    // Numéro de téléphone
    document.querySelectorAll('input[name="telephone"]').forEach(validatePhoneInput);
    // Alphanumérique (adresse, ville)
    document.querySelectorAll('input[name="adresse"], input[name="ville"]').forEach(input => {
        input.removeEventListener('keypress', onlyAlphaNum);
        input.addEventListener('keypress', onlyAlphaNum);
    });
    // Titre de film
    document.querySelectorAll('input[name="titre"]').forEach(input => {
        input.removeEventListener('keypress', onlyTitleChars);
        input.addEventListener('keypress', onlyTitleChars);
    });
    // Barre de recherche
    document.querySelectorAll('input[name="search"]').forEach(input => {
        input.removeEventListener('keypress', onlySearchChars);
        input.addEventListener('keypress', onlySearchChars);
    });
    // Email
    document.querySelectorAll('input[type="email"]').forEach(input => {
        input.removeEventListener('blur', function() {
            if (this.value && !this.value.match(/^[^@\s]+@[^@\s]+\.[^@\s]+$/)) {
                alert('Email invalide.');
                this.focus();
            }
        });
    });
    // Mot de passe
    if (pwdInput) {
        pwdInput.removeEventListener('blur', function() {
            if (!/^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/.test(this.value)) {
                alert('Le mot de passe doit contenir au moins 8 caractères, dont une lettre et un chiffre.');
                this.focus();
            }
        });
    }
    // Numérique pur (quantité, prix)
    document.querySelectorAll('input[name="quantite"], input[name="prix"]').forEach(input => {
        input.removeEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    });
}

// Appel initial
applyValidation();

// ===============================
// FIN VALIDATION JS GLOBALE
// =============================== 