//Style pour les inputs de connexion
document.addEventListener('DOMContentLoaded', function() {
const passwordInput = document.getElementById('password');
const eyeIcon = document.querySelector('.toggle-password');

// Ajouter la classe "active" lorsque l'utilisateur clique sur l'input (focus)
passwordInput.addEventListener('focus', function() {
    eyeIcon.classList.add('active');
});

// Vérifier si le champ contient du texte lors de l'événement blur (perte de focus)
passwordInput.addEventListener('blur', function() {
    if (passwordInput.value === "") {
        eyeIcon.classList.remove('active');
    }
});

// Ajouter la classe "active" lorsque l'utilisateur tape dans l'input (input)
passwordInput.addEventListener('input', function() {
    if (passwordInput.value !== "") {
        eyeIcon.classList.add('active');
    }
});
});;

//Style pour choisir l'heure
// Vérifier si nous sommes sur la page "pre-admission.php"
if (window.location.pathname.includes('pre_admission.php')) {
    flatpickr("#heure_hospitalisation", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "H:i",
        time_24hr: true,
    });
}


//Pour voir le mdp avec l'oeil
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eye-icon');

    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
}

//Aller à l'étape d'avant pdt la pré-admssion
function goToPreviousStep() {
    const form = document.createElement('form');
    form.method = 'post';
    form.action = '';

    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = 'submit_prec';
    input.value = '1';

    form.appendChild(input);
    document.body.appendChild(form);
    form.submit();
}

//Copier les données d'un patient existant déja
document.addEventListener("DOMContentLoaded", function () {
    const numeroSecuInput = document.getElementById("numero_secu_verif");

    // Sélectionner tous les boutons qui doivent déclencher l'enregistrement
    const buttons = document.querySelectorAll('.verif-button'); // Assurez-vous que les boutons ont la classe "verif-button"

    // Ajouter un écouteur d'événement "click" pour chaque bouton
    buttons.forEach(button => {
        button.addEventListener("click", function () {
            const numeroSecu = numeroSecuInput.value;

            if (numeroSecu) {
                fetch(`verifier_patient.php?numero_secu=${numeroSecu}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const patient = data.patient;
                            // Récupérer la valeur de 'step' depuis l'attribut 'data-step'
                            const step = document.getElementById("session-step").getAttribute("data-step");
                            console.log(step);  // Affiche la valeur de $_SESSION['step']

                            if (step === "2") {
                                // Remplir les champs du formulaire patient
                                document.getElementById("nom").value = patient.nom || '';
                                document.getElementById("nom_epouse").value = patient.nom_epouse || '';
                                document.getElementById("prenom").value = patient.prenom || '';
                                document.getElementById("civilite").value = patient.civilite || '';
                                document.getElementById("date_naissance").value = patient.date_naissance || '';
                                document.getElementById("adresse").value = patient.adresse || '';
                                document.getElementById("code_postal").value = patient.code_postal || '';
                                document.getElementById("ville").value = patient.ville || '';
                                document.getElementById("email").value = patient.email || '';
                                document.getElementById("telephone").value = patient.telephone || '';
                            } else if (step === "3") {
                                document.getElementById("nom_personne_p").value = patient.nom_personne_p || '';
                                document.getElementById("prenom_personne_p").value = patient.prenom_personne_p || '';
                                document.getElementById("tel_personne_p").value = patient.tel_personne_p || '';
                                document.getElementById("adresse_personne_p").value = patient.adresse_personne_p || '';
                            } else if (step === "4") {
                                document.getElementById("nom_personne_c").value = patient.nom_personne_c || '';
                                document.getElementById("prenom_personne_c").value = patient.prenom_personne_c || '';
                                document.getElementById("tel_personne_c").value = patient.tel_personne_c || '';
                                document.getElementById("adresse_personne_c").value = patient.adresse_personne_c || '';
                            } else if (step === "5") {
                                document.getElementById("numero_secu").value = patient.numero_secu || '';
                                document.getElementById("organisme").value = patient.organisme || '';
                                document.getElementById("patient_assurance").value = patient.patient_assurance || '';
                                document.getElementById("patient_ald").value = patient.patient_ald || '';
                                document.getElementById("nom_mutuelle").value = patient.nom_mutuelle || '';
                                document.getElementById("num_adherent").value = patient.num_adherent || '';
                            }

                        } else {
                            alert(data.message);
                            // Réinitialiser tous les champs
                            document.querySelectorAll("input, select").forEach(input => input.value = "");
                        }
                    })
                    .catch(error => console.error("Erreur :", error));
            }
        }); // Fin de l'écouteur du bouton
    }); // Fin de la boucle forEach des boutons
}); // Fin du document.addEventListener



// Fonction pour afficher le nom du fichier choisi
function displayFileName(inputId) {
    console.log(inputId);
    var fileInput = document.getElementById(inputId);
    var fileName = fileInput.files.length > 0 ? fileInput.files[0].name : "";
    var messageElement = document.getElementById(inputId + "_msg");

    if (fileName) {
        messageElement.textContent = fileName;
        messageElement.style.color = "white";
        messageElement.style.fontFamily = "Lucida Sans";
        messageElement.style.marginTop = "3%";
    } else {
        messageElement.textContent = "";
    }
}

//Copie les données de la personne de confiance
var personnePData = document.getElementById('personne_p_data');
if (personnePData) { // Vérifie si l'élément existe avant de l'utiliser
    var nomPersonneP = personnePData.getAttribute('data-nom-personne-p');
    var prenomPersonneP = personnePData.getAttribute('data-prenom-personne-p');
    var telPersonneP = personnePData.getAttribute('data-tel-personne-p');
    var adressePersonneP = personnePData.getAttribute('data-adresse-personne-p');

    function copyPersonneData() {
        var isChecked = document.getElementById('sameAsPrev').checked;

        if (isChecked) {
            document.getElementById('nom_personne_c').value = nomPersonneP;
            document.getElementById('prenom_personne_c').value = prenomPersonneP;
            document.getElementById('tel_personne_c').value = telPersonneP;
            document.getElementById('adresse_personne_c').value = adressePersonneP;
        } else {
            document.getElementById('nom_personne_c').value = '';
            document.getElementById('prenom_personne_c').value = '';
            document.getElementById('tel_personne_c').value = '';
            document.getElementById('adresse_personne_c').value = '';
        }
    }
};


//Déconnexion au bout de 15 minutes
let timeout;

function resetTimer() {
    clearTimeout(timeout);
    timeout = setTimeout(logout, 45 * 60 * 1000); // 15 minutes
}

function logout() {
    alert("Votre session a expiré !");
    window.location.href = "logout.php"; // Change selon ton besoin
}

// Détection d'activité
window.addEventListener("load", resetTimer);
document.addEventListener("mousemove", resetTimer);
document.addEventListener("keydown", resetTimer);
document.addEventListener("click", resetTimer);
document.addEventListener("scroll", resetTimer);
