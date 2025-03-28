<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <h2>
                <?php 
                if ($_SESSION['id_role'] == 1) {
                    echo "Panel Admin";
                } elseif ($_SESSION['id_role'] == 2) {
                    echo "Panel Secrétaire";
                } else {
                    echo "Panel Utilisateur";
                }
                ?>
            </h2>
        </div>
        <nav class="sidebar-menu">
            <a href="pre_admission.php" class="first-child">Pré admission</a>
            <a href="hospitalisation_table.php">Hospitalisation</a>
            <a href="patient_table.php">Patient</a>
            <?php if ($_SESSION['id_role'] == 1) { ?>
                <a href="service_table.php">Service</a>
                <a href="personnel_table.php">Personnel</a>
            <?php } ?>            
        </nav>
        <a href="logout.php" class="logout"><img src="Image/logout"></a>
    </div>

    <head>
        <style>       
            /* ----- SIDEBAR STYLES ----- */
            .sidebar {
                min-width: 13%;
                height: 96vh;
                background: linear-gradient(135deg, #0056b3,rgb(0, 105, 217));
                color: white;
                display: flex;
                flex-direction: column;
                justify-content: space-between;
                align-items: center;
                text-align: center;
                position: sticky;
                top: 0;
                left: 0;
                padding: 20px 0;
                box-shadow: 4px 0px 10px rgba(0, 0, 0, 0.2);
                border-radius: 0 15px 15px 0;
            }

            /* ----- HEADER ----- */
            .sidebar-header h2 {
                font-family: "Bebas Neue", sans-serif;
                font-size: 2.5vw;
                color: white;
                text-transform: uppercase;
                letter-spacing: 2px;
                padding: 15px 0;
                margin: 0 auto;
                border-bottom: 2px solid rgba(255, 255, 255, 0.3);
                width: 80%;
            }

            /* ----- MENU LINKS ----- */
            .sidebar-menu a {
                width: 85%;
                color: white;
                text-decoration: none;
                padding: 12px 15px;
                display: block;
                border-radius: 8px;
                font-size: 1.4vw;
                font-family: "Roboto", sans-serif;
                font-weight: bold;
                transition: all 0.3s ease-in-out;
            }

            /* Bordure sous le premier élément */
            .first-child {
                border-bottom: 2px solid rgba(255, 255, 255, 0.3);
            }

            /* Effet au survol */
            .sidebar-menu a:hover {
                transform: translateX(10px);
            }

            /* Lien actif (ajoute une classe active via PHP si nécessaire) */
            .sidebar-menu a.active {
                background: rgba(255, 255, 255, 0.4);
                border-left: 4px solid #ffeb3b;
                transform: translateX(5px);
            }

            /* ----- LOGOUT BUTTON ----- */
            .logout {
                width: 50%;
                padding: 12px 15px;
                margin-bottom: 20px;
                border-radius: 8px;
                font-weight: bold;
                color: white;
                text-decoration: none;
                transition: all 0.3s ease-in-out;
            }

            .logout:hover {
                transform: scale(1.05);
            }

            .logout img{
                width: 4vw;
                object-fit: cover;
            }

        </style>
    </head>
</body>
