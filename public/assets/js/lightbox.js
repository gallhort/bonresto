/**
 * LIGHTBOX AVEC NAVIGATION
 */

// Variables globales pour la navigation
let currentPhotos = [];
let currentPhotoIndex = 0;

function openLightbox(reviewId, photoIndex) {
    // Trouver la carte de l'avis
    const reviewCard = document.querySelector('.review-card[data-review-id="' + reviewId + '"], .review-item[data-review-id="' + reviewId + '"]');

    if (!reviewCard) {
        alert('Avis non trouvé');
        return;
    }

    // Récupérer les photos
    const photoElements = reviewCard.querySelectorAll('.review-photo-item img');

    if (photoElements.length === 0) {
        alert('Aucune photo');
        return;
    }

    currentPhotos = Array.from(photoElements).map(function(img) {
        return img.src;
    });

    currentPhotoIndex = photoIndex;

    // Créer la lightbox si elle n'existe pas
    let lightbox = document.getElementById('simpleLightbox');

    if (!lightbox) {
        const html = '<div id="simpleLightbox" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.95); z-index:99999; align-items:center; justify-content:center;">' +
            '<button onclick="closeLightbox()" style="position:absolute; top:20px; right:20px; width:50px; height:50px; border-radius:50%; background:rgba(255,255,255,0.2); border:none; color:white; font-size:30px; cursor:pointer; z-index:10;">×</button>' +
            '<button id="prevBtn" onclick="prevPhoto()" style="position:absolute; left:20px; top:50%; transform:translateY(-50%); width:50px; height:50px; border-radius:50%; background:rgba(255,255,255,0.2); border:none; color:white; font-size:30px; cursor:pointer; z-index:10; display:flex; align-items:center; justify-content:center;">‹</button>' +
            '<button id="nextBtn" onclick="nextPhoto()" style="position:absolute; right:20px; top:50%; transform:translateY(-50%); width:50px; height:50px; border-radius:50%; background:rgba(255,255,255,0.2); border:none; color:white; font-size:30px; cursor:pointer; z-index:10; display:flex; align-items:center; justify-content:center;">›</button>' +
            '<img loading="lazy" id="simpleLightboxImg" style="max-width:90vw; max-height:90vh; display:block;">' +
            '<div id="simpleLightboxCounter" style="position:absolute; top:20px; left:50%; transform:translateX(-50%); background:rgba(0,0,0,0.7); color:white; padding:10px 20px; border-radius:20px; font-size:14px; z-index:10;"></div>' +
            '</div>';

        document.body.insertAdjacentHTML('beforeend', html);
        lightbox = document.getElementById('simpleLightbox');

        // Navigation clavier
        document.addEventListener('keydown', function(e) {
            if (lightbox.style.display !== 'flex') return;

            if (e.key === 'Escape') {
                closeLightbox();
            } else if (e.key === 'ArrowLeft') {
                prevPhoto();
            } else if (e.key === 'ArrowRight') {
                nextPhoto();
            }
        });

        // Fermer en cliquant sur le fond
        lightbox.addEventListener('click', function(e) {
            if (e.target.id === 'simpleLightbox') {
                closeLightbox();
            }
        });
    }

    // Afficher la photo
    showPhoto(currentPhotoIndex);

    lightbox.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function showPhoto(index) {
    const img = document.getElementById('simpleLightboxImg');
    const counter = document.getElementById('simpleLightboxCounter');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');

    if (!img || !counter) return;

    // Mettre à jour l'index
    currentPhotoIndex = index;

    // Boucler si nécessaire
    if (currentPhotoIndex < 0) {
        currentPhotoIndex = currentPhotos.length - 1;
    }
    if (currentPhotoIndex >= currentPhotos.length) {
        currentPhotoIndex = 0;
    }

    // Afficher la photo
    img.src = currentPhotos[currentPhotoIndex];
    counter.textContent = (currentPhotoIndex + 1) + ' / ' + currentPhotos.length;

    // Masquer les boutons si 1 seule photo
    if (prevBtn && nextBtn) {
        if (currentPhotos.length <= 1) {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
        } else {
            prevBtn.style.display = 'flex';
            nextBtn.style.display = 'flex';
        }
    }
}

function prevPhoto() {
    showPhoto(currentPhotoIndex - 1);
}

function nextPhoto() {
    showPhoto(currentPhotoIndex + 1);
}

function closeLightbox() {
    const lightbox = document.getElementById('simpleLightbox');
    if (lightbox) {
        lightbox.style.display = 'none';
        document.body.style.overflow = '';
    }
}
