// Modal admin
const adminBtn = document.getElementById('adminBtn');
const adminModal = document.getElementById('adminModal');
const closeModal = document.getElementById('closeModal');

if (adminBtn && adminModal && closeModal) {
  adminBtn.onclick = () => adminModal.style.display = 'flex';
  closeModal.onclick = () => adminModal.style.display = 'none';
  window.onclick = (e) => {
    if (e.target === adminModal) adminModal.style.display = 'none';
  };
}

// Ajout au panier depuis films.php
if (document.querySelector('.films-container')) {
  const buttons = document.querySelectorAll('.film-card .add-cart-btn');
  buttons.forEach((btn) => {
    btn.addEventListener('click', () => {
      const filmCard = btn.closest('.film-card');
      const filmId = filmCard.dataset.id;
      const originalText = btn.textContent;
      btn.textContent = 'Ajout en cours...';
      btn.disabled = true;
      fetch('traitement_panier.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ operation: 'add', id_film: filmId })
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          btn.textContent = 'Ajouté !';
          const notification = document.createElement('div');
          notification.className = 'notification success';
          notification.textContent = data.message || 'Film ajouté au panier !';
          document.body.appendChild(notification);
          setTimeout(() => {
            notification.remove();
            btn.textContent = originalText;
            btn.disabled = false;
          }, 2000);
          updatePanierBadge();
        } else {
          btn.textContent = 'Erreur';
          const notification = document.createElement('div');
          notification.className = 'notification error';
          notification.textContent = data.message || 'Erreur lors de l\'ajout au panier';
          document.body.appendChild(notification);
          setTimeout(() => {
            notification.remove();
            btn.textContent = originalText;
            btn.disabled = false;
          }, 2000);
        }
      })
      .catch(error => {
        btn.textContent = 'Erreur';
        const notification = document.createElement('div');
        notification.className = 'notification error';
        notification.textContent = 'Erreur de connexion';
        document.body.appendChild(notification);
        setTimeout(() => {
          notification.remove();
          btn.textContent = originalText;
          btn.disabled = false;
        }, 2000);
      });
    });
  });
}

// Remplir la liste des clients existants
if (document.getElementById('clientSelect')) {
  fetch('get_clients.php')
    .then(res => res.json())
    .then(clients => {
      const select = document.getElementById('clientSelect');
      select.innerHTML = '<option value="">Sélectionner un client</option>';
      clients.forEach(c => {
        const opt = document.createElement('option');
        opt.value = c.id_client;
        opt.textContent = c.prenom + ' ' + c.nom + ' (' + c.contact + ')';
        select.appendChild(opt);
      });
    });
}

// Gestion du formulaire client
if (document.getElementById('client_type')) {
  document.getElementById('client_type').addEventListener('change', function() {
    const newClientFields = document.getElementById('new_client_fields');
    const existingClientFields = document.getElementById('existing_client_fields');
    const newClientInputs = newClientFields.querySelectorAll('input');
    const existingClientSelect = document.getElementById('id_client');

    if (this.value === 'new') {
      newClientFields.style.display = 'block';
      existingClientFields.style.display = 'none';
      newClientInputs.forEach(input => input.required = true);
      existingClientSelect.required = false;
    } else {
      newClientFields.style.display = 'none';
      existingClientFields.style.display = 'block';
      newClientInputs.forEach(input => input.required = false);
      existingClientSelect.required = true;
    }
  });
}

// Gestion du panier
function updateQuantite(idFilm, delta) {
  fetch('traitement_panier.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      operation: 'update',
      id_film: idFilm,
      quantite: delta
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      window.location.reload();
    } else {
      alert(data.message || 'Erreur lors de la mise à jour du panier');
    }
  })
  .catch(error => {
    alert('Erreur de connexion');
  });
}

function removeFilm(idFilm) {
  if (confirm('Voulez-vous vraiment retirer ce film du panier ?')) {
    fetch('traitement_panier.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        operation: 'remove',
        id_film: idFilm
      })
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        window.location.reload();
      } else {
        alert(data.message || 'Erreur lors de la suppression du film');
      }
    })
    .catch(error => {
      alert('Erreur de connexion');
    });
  }
}

//Recherche dans la liste de factures
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('facture-search-input');
    if (searchInput) {
      console.log('Recherche activé');
        searchInput.addEventListener('input', function() {
            const search = this.value.toLowerCase();
            document.querySelectorAll('#factures-container .facture-card').forEach(card => {
                // Récupère le nom/prénom sans le préfixe "Client : "
                let client = card.querySelector('.client').textContent.toLowerCase().replace('client : ', '');
                // Récupère la date affichée (ex: 12/05/2024)
                let dateAffichee = card.querySelector('.date').textContent.toLowerCase();
                // Récupère la date brute (ex: 2024-05-12) via data-date
                let dateBrute = card.getAttribute('data-date') ? card.getAttribute('data-date').toLowerCase() : '';
                if (
                    client.includes(search) ||
                    dateAffichee.includes(search) ||
                    dateBrute.includes(search)
                ) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }else {
      console.log('Champ de recherche non trouvé');
  }
});

// --- Badge Panier ---
function updatePanierBadge() {
    fetch('get_panier_count.php')
        .then(res => res.json())
        .then(data => {
            const badge = document.getElementById('panier-badge');
            if (badge) {
                if (data.count > 0) {
                    badge.textContent = data.count;
                    badge.style.display = 'inline-block';
                } else {
                    badge.style.display = 'none';
                }
            }
        });
}
document.addEventListener('DOMContentLoaded', updatePanierBadge);

// Gestion des paramètres et modals
document.addEventListener('DOMContentLoaded', function() {
    // Éléments du popup principal
    const settingsBtn = document.getElementById('settingsBtn');
    const settingsModal = document.getElementById('settingsModal');
    const closeSettingsModal = document.getElementById('closeSettingsModal');
    const btnInfosAdmin = document.getElementById('btnInfosAdmin');
    const btnModifierInfos = document.getElementById('btnModifierInfos');
    const btnHistoriqueVentes = document.getElementById('btnHistoriqueVentes');
    const btnLogout = document.getElementById('btnLogout');

    // Sous-popups
    const infosAdminModal = document.getElementById('infosAdminModal');
    const closeInfosAdminModal = document.getElementById('closeInfosAdminModal');
    const modifierInfosModal = document.getElementById('modifierInfosModal');
    const closeModifierInfosModal = document.getElementById('closeModifierInfosModal');
    const historiqueVentesModal = document.getElementById('historiqueVentesModal');
    const closeHistoriqueVentesModal = document.getElementById('closeHistoriqueVentesModal');

    // Gestion du popup principal
    if (settingsBtn && settingsModal) {
        settingsBtn.onclick = () => settingsModal.style.display = 'flex';
        closeSettingsModal.onclick = () => settingsModal.style.display = 'none';
        window.addEventListener('click', (e) => {
            if (e.target === settingsModal) settingsModal.style.display = 'none';
            if (e.target === infosAdminModal) infosAdminModal.style.display = 'none';
            if (e.target === modifierInfosModal) modifierInfosModal.style.display = 'none';
            if (e.target === historiqueVentesModal) historiqueVentesModal.style.display = 'none';
        });
    }

    // Gestion du popup Infos admin
    if (btnInfosAdmin && infosAdminModal) {
        btnInfosAdmin.onclick = () => {
            settingsModal.style.display = 'none';
            infosAdminModal.style.display = 'flex';
        };
        closeInfosAdminModal.onclick = () => infosAdminModal.style.display = 'none';
    }

    // Gestion du popup Modifier infos
    if (btnModifierInfos && modifierInfosModal) {
        btnModifierInfos.onclick = () => {
            settingsModal.style.display = 'none';
            modifierInfosModal.style.display = 'flex';
        };
        closeModifierInfosModal.onclick = () => modifierInfosModal.style.display = 'none';
    }

    // Gestion du popup Historique de ventes
    if (btnHistoriqueVentes && historiqueVentesModal) {
        btnHistoriqueVentes.onclick = () => {
            settingsModal.style.display = 'none';
            historiqueVentesModal.style.display = 'flex';
        };
        closeHistoriqueVentesModal.onclick = () => historiqueVentesModal.style.display = 'none';
    }

    // Gestion du bouton de déconnexion
    if (btnLogout) {
        btnLogout.onclick = () => { window.location.href = 'logout.php'; };
    }

    // Gestion du formulaire de modification des informations
    const modifierInfosForm = document.getElementById('modifierInfosForm');
    if (modifierInfosForm) {
        modifierInfosForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password || confirmPassword) {
                if (password !== confirmPassword) {
                    e.preventDefault();
                    alert('Les mots de passe ne correspondent pas');
                }
                if (password.length < 6) {
                    e.preventDefault();
                    alert('Le mot de passe doit contenir au moins 6 caractères');
                }
            }
        });
    }
}); 