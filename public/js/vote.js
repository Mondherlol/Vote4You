'use strict';

var tinderContainer = document.querySelector('.tinder');
var allCards = document.querySelectorAll('.tinder--card');
var nope = document.getElementById('nope');
var love = document.getElementById('love');

var selectedCards = [];

function initCards(card, index) {
    var newCards = document.querySelectorAll('.tinder--card:not(.removed)');

    newCards.forEach(function (card, index) {
        card.style.zIndex = allCards.length - index;
        card.style.transform = 'scale(' + (20 - index) / 20 + ') translateY(-' + 30 * index + 'px)';
        card.style.opacity = (10 - index) / 10;
    });

    tinderContainer.classList.add('loaded');
}

initCards();

function showToast(message, type = 'info') {
    Toastify({
        text: message,
        duration: type == 'success' ? 4000 : 1500,
        newWindow: true,
        close: true,
        gravity: "bottom",
        position: 'center',
        stopOnFocus: true, // Prevents dismissing of toast on hover
        style: {
            background:  type == 'success' ? "linear-gradient(to right, #00b09b, #96c93d)" : "linear-gradient(to right, #ff416c, #ff4b2b)",
        },
    }).showToast();
}

allCards.forEach(function (el) {
    var hammertime = new Hammer(el);

    hammertime.on('pan', function (event) {
        el.classList.add('moving');
    });

    hammertime.on('pan', function (event) {
        if (event.deltaX === 0) return;
        if (event.center.x === 0 && event.center.y === 0) return;

        tinderContainer.classList.toggle('tinder_love', event.deltaX > 0);
        tinderContainer.classList.toggle('tinder_nope', event.deltaX < 0);
        tinderContainer.style.zIndex =100;
        var xMulti = event.deltaX * 0.03;
        var yMulti = event.deltaY / 80;
        var rotate = xMulti * yMulti;

        event.target.style.transform = 'translate(' + event.deltaX + 'px, ' + event.deltaY + 'px) rotate(' + rotate + 'deg)';
    });

    hammertime.on('panend', function (event) {
        el.classList.remove('moving');
        tinderContainer.classList.remove('tinder_love');
        tinderContainer.classList.remove('tinder_nope');

        // remettre le zindex à 0 genre 0.2 seconde apres
        setTimeout(() => {
            tinderContainer.style.zIndex = 0;
        }, 200);

        var moveOutWidth = document.body.clientWidth - 500;
        var keep = Math.abs(event.deltaX) < 80 || Math.abs(event.velocityX) < 0.5;

        event.target.classList.toggle('removed', !keep);

        if (keep) {
            event.target.style.transform = '';
        } else {
            var endX = Math.max(Math.abs(event.velocityX) * moveOutWidth, moveOutWidth);
            var toX = event.deltaX > 0 ? endX : -endX;
            var endY = Math.abs(event.velocityY) * moveOutWidth;
            var toY = event.deltaY > 0 ? endY : -endY;
            var xMulti = event.deltaX * 0.03;
            var yMulti = event.deltaY / 80;
            var rotate = xMulti * yMulti;

            event.target.style.transform = 'translate(' + toX + 'px, ' + (toY + event.deltaY) + 'px) rotate(' + rotate + 'deg)';
            initCards();

            // Ajouter aux votes positifs si le choix est "love"
            if (event.deltaX > 0) {
                var choiceId = el.getAttribute('data-choice-id');
                if (!selectedCards.includes(choiceId)) {
                    selectedCards.push(choiceId);
                }
            }

            // Vérifier si c'est la dernière carte
            var remainingCards = document.querySelectorAll('.tinder--card:not(.removed)');
            if (!remainingCards.length) {
                handleLastCard();
            }
        }
    });
});

function createButtonListener(love) {
    return function (event) {
        var cards = document.querySelectorAll('.tinder--card:not(.removed)');
        var moveOutWidth = document.body.clientWidth * 1.5;

        if (!cards.length) return false;

        var card = cards[0];
        card.classList.add('removed');

        if (love) {
            card.style.transform = 'translate(' + moveOutWidth + 'px, -100px) rotate(-30deg)';
            var choiceId = card.getAttribute('data-choice-id');
            if (!selectedCards.includes(choiceId)) {
                selectedCards.push(choiceId);
            }
        } else {
            card.style.transform = 'translate(-' + moveOutWidth + 'px, -100px) rotate(30deg)';
        }

        initCards();

        // Vérifier si c'est la dernière carte
        var remainingCards = document.querySelectorAll('.tinder--card:not(.removed)');
        if (!remainingCards.length) {
            handleLastCard();
        }

        event.preventDefault();
    };
}

function handleLastCard() {
    if (selectedCards.length > 0) {
        console.log('Votes positifs :', selectedCards);



        // Ajouter les choix sélectionnés dans le champ du formulaire
        document.getElementById('selected-choices').value = JSON.stringify(selectedCards);

        // Soumettre automatiquement le formulaire
        document.getElementById('vote-form').submit();
        showToast('Vote enregistré avec succès ! ', 'success');

    } else {
        showToast("Aucun vote positif ! On recommence.", 'error');
        resetCards();
    }
}

function resetCards() {
    allCards.forEach(function (card) {
        card.classList.remove('removed');
        card.style.transform = '';
    });
    selectedCards = [];
    initCards();
}

var nopeListener = createButtonListener(false);
var loveListener = createButtonListener(true);

nope.addEventListener('click', nopeListener);
love.addEventListener('click', loveListener);

document.addEventListener('DOMContentLoaded', function () {
    const urlParams = new URLSearchParams(window.location.search);
    const successMessage = urlParams.get('success'); // Récupère le message de succès dans la query string

    if (successMessage) {
        // Affiche le toast avec le message de succès
        showToast(successMessage, 'success');
    }
});
