<?php
require_once 'config.php';
$message = '';

// Gestion de la suppression d'un fichier
if (isset($_GET['delete']) && !empty($_GET['delete'])) {
    $file_id = intval($_GET['delete']);
    
    // Récupérer les informations du fichier avant suppression
    $sql = "SELECT chemin_fichier FROM fichiers WHERE id = ?";
    if ($stmt = mysqli_prepare($conn, $sql)) {
        mysqli_stmt_bind_param($stmt, "i", $file_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        if ($file = mysqli_fetch_assoc($result)) {
            // Supprimer le fichier physique
            if (file_exists($file['chemin_fichier'])) {
                unlink($file['chemin_fichier']);
            }
            
            // Supprimer l'entrée de la base de données
            $sql_delete = "DELETE FROM fichiers WHERE id = ?";
            if ($stmt_delete = mysqli_prepare($conn, $sql_delete)) {
                mysqli_stmt_bind_param($stmt_delete, "i", $file_id);
                if (mysqli_stmt_execute($stmt_delete)) {
                    $message = "Le fichier a été supprimé avec succès.";
                    $message_class = 'success';
                } else {
                    $message = "Erreur lors de la suppression du fichier de la base de données: " . mysqli_error($conn);
                    $message_class = 'error';
                }
                mysqli_stmt_close($stmt_delete);
            }
        } else {
            $message = "Fichier introuvable.";
            $message_class = 'error';
        }
        mysqli_stmt_close($stmt);
    }
    
    // Rediriger pour éviter la resoumission du formulaire
    header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . "?message=" . urlencode($message) . "&message_class=" . $message_class);
    exit();
}

// Vérifier s'il y a un message à afficher
if (isset($_GET['message'])) {
    $message = urldecode($_GET['message']);
    $message_class = isset($_GET['message_class']) ? $_GET['message_class'] : 'info';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $target_dir = "uploads/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $target_file = $target_dir . basename($_FILES["fichier"]["name"]);
    $uploadOk = 1;
    $fileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // Vérifier si le fichier existe déjà
    if (file_exists($target_file)) {
        $message = "Désolé, ce fichier existe déjà.";
        $uploadOk = 0;
    }
    
    // Vérifier la taille du fichier (max 10MB)
    if ($_FILES["fichier"]["size"] > 10000000) {
        $message = "Désolé, votre fichier est trop volumineux.";
        $uploadOk = 0;
    }
    
    // Autoriser certains formats de fichiers
    $allowed_types = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png');
    if (!in_array($fileType, $allowed_types)) {
        $message = "Désolé, seuls les fichiers PDF, DOC, DOCX, XLS, XLSX, JPG, JPEG, PNG sont autorisés.";
        $uploadOk = 0;
    }
    
    // Vérifier si $uploadOk est à 0 à cause d'une erreur
    if ($uploadOk == 0) {
        $message = "Désolé, votre fichier n'a pas été téléchargé. " . $message;
    } else {
        if (move_uploaded_file($_FILES["fichier"]["tmp_name"], $target_file)) {
            // Préparer et exécuter la requête d'insertion
            $nom_fichier = basename($_FILES["fichier"]["name"]);
            $description = $_POST['description'];
            $taille_fichier = $_FILES["fichier"]["size"];
            $chemin_fichier = $target_file;
            
            $sql = "INSERT INTO fichiers (nom_fichier, type_fichier, taille_fichier, description, chemin_fichier) 
                    VALUES (?, ?, ?, ?, ?)";
                    
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "ssiss", $nom_fichier, $fileType, $taille_fichier, $description, $chemin_fichier);
                
                if (mysqli_stmt_execute($stmt)) {
                    $message = "Le fichier " . htmlspecialchars($nom_fichier) . " a été téléchargé avec succès.";
                } else {
                    $message = "Erreur lors de l'enregistrement en base de données: " . mysqli_error($conn);
                }
                
                mysqli_stmt_close($stmt);
            }
        } else {
            $message = "Désolé, une erreur s'est produite lors du téléchargement de votre fichier.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - Upload de fichiers | EMG</title>
    <meta name="description" content="Espace d'administration pour le téléversement de fichiers - Empyra Mining Group">
    <link rel="stylesheet" href="style_global.css">
    <link rel="stylesheet" href="telechargement.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .admin-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .btn-action {
            display: inline-block;
            padding: 6px 10px;
            margin: 0 3px;
            border-radius: 4px;
            color: #555;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .btn-action:hover {
            background-color: #f5f5f5;
        }
        
        .btn-delete {
            color: #e74c3c;
        }
        
        .btn-delete:hover {
            background-color: #fde8e8;
        }
        
        .files-table {
            margin-top: 20px;
            border: 1px solid #eee;
            border-radius: 8px;
            overflow: hidden;
        }
        
        .files-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .files-table th, 
        .files-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .files-table tbody tr:hover {
            background-color: #f9f9f9;
        }
        
        .upload-form {
            margin-top: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2C3E50;
            font-weight: 500;
        }
        
        .form-group input[type="file"], 
        .form-group textarea,
        .form-group input[type="text"] {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            transition: border-color 0.3s;
        }
        
        .form-group input[type="file"] {
            padding: 8px;
        }
        
        .form-group input:focus, 
        .form-group textarea:focus {
            outline: none;
            border-color: #C9A13A;
            box-shadow: 0 0 0 2px rgba(201, 161, 58, 0.2);
        }
        
        .btn-upload {
            background: #C9A13A;
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: background 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-upload:hover {
            background: #b38f35;
        }
        
        .message {
            padding: 15px;
            margin: 0 0 20px 0;
            border-radius: 5px;
            font-weight: 500;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #C9A13A;
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link:hover {
            text-decoration: underline;
        }
        .form-group textarea {
            height: 100px;
            resize: vertical;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .file-list {
            margin-top: 30px;
        }
        .file-list h3 {
            color: #2C3E50;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #C9A13A;
        }
        .action-buttons a {
            color: #C9A13A;
            margin-right: 10px;
            text-decoration: none;
        }
        .action-buttons a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="images/Logo EMG.png" width="100px" height="100px" alt="Logo">
        </div>
        <nav class="navbar">
            <div class="burger" id="burger-menu">
                <span></span>
                <span></span>
                <span></span>
            </div>
            <ul class="nav-links" id="nav-links">
                <li class="item-menu"><a href="admin_upload.php" class="active">InsertionDocucument</a></li>
                <li class="item-menu"><a href="admin_upload_Actu.php">InsertionActualités</a></li>
            </ul>
        </nav>
    </header>
    
    <div class="admin-container">
        <h2><i class="fas fa-upload"></i> Téléverser un nouveau fichier</h2>
        
        <?php if (!empty($message)): ?>
            <div class="message <?php echo isset($message_class) ? $message_class : (strpos($message, 'succès') !== false ? 'success' : 'error'); ?>">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="upload-form">
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="fichier">Sélectionner un fichier (max 10MB) :</label>
                    <input type="file" name="fichier" id="fichier" required accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.txt">
                    <small class="text-muted">Formats acceptés : PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, JPG, JPEG, PNG, TXT</small>
                </div>
                
                <div class="form-group">
                    <label for="description">Description :</label>
                    <textarea name="description" id="description" rows="4" placeholder="Ajoutez une description détaillée du fichier..."></textarea>
                </div>
            
            <div class="form-actions">
                <button type="submit" class="btn-upload">
                    <i class="fas fa-upload"></i> Téléverser le fichier
                </button>
                <a href="telechargement.php" class="back-link">
                    <i class="fas fa-arrow-left"></i> Retour aux téléchargements
                </a>
            </div>
            </form>
        </div>
        
        <div style="margin-top: 40px;">
            <h3><i class="fas fa-list"></i> Liste des fichiers</h3>
            <?php
            $files_query = "SELECT id, nom_fichier, type_fichier, taille_fichier, date_upload, description FROM fichiers ORDER BY date_upload DESC";
            $recent_files = mysqli_query($conn, $files_query);
            
            if (mysqli_num_rows($recent_files) > 0): ?>
                <div class="files-table" style="margin-top: 20px; overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background-color: #f5f5f5;">
                                <th style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">Nom du fichier</th>
                                <th style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">Type</th>
                                <th style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">Taille</th>
                                <th style="padding: 12px; text-align: left; border-bottom: 1px solid #ddd;">Date d'ajout</th>
                                <th style="padding: 12px; text-align: right; border-bottom: 1px solid #ddd;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($file = mysqli_fetch_assoc($recent_files)): 
                                $file_icon = getFileIcon($file['type_fichier']);
                                $file_size = formatFileSizeDB($file['taille_fichier']);
                            ?>
                                <tr style="border-bottom: 1px solid #eee;">
                                    <td style="padding: 12px;">
                                        <i class="<?php echo $file_icon; ?>" style="color: #C9A13A; margin-right: 8px;"></i>
                                        <?php echo htmlspecialchars($file['nom_fichier']); ?>
                                        <?php if (!empty($file['description'])): ?>
                                            <br><small style="color: #666;"><?php echo htmlspecialchars($file['description']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td style="padding: 12px;"><?php echo strtoupper($file['type_fichier']); ?></td>
                                    <td style="padding: 12px;"><?php echo $file_size; ?></td>
                                    <td style="padding: 12px;"><?php echo date('d/m/Y H:i', strtotime($file['date_upload'])); ?></td>
                                    <td style="padding: 12px; text-align: right;">
                                        <a href="download.php?file=<?php echo urlencode($file['nom_fichier']); ?>" class="btn-action" title="Télécharger">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        <a href="#" onclick="confirmDelete(<?php echo $file['id']; ?>, '<?php echo addslashes(htmlspecialchars($file['nom_fichier'])); ?>')" class="btn-action btn-delete" title="Supprimer">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p style="color: #666; font-style: italic; margin-top: 20px;">Aucun fichier n'a encore été téléversé.</p>
            <?php endif; ?>
            
            <?php
            // Fonction pour obtenir l'icône appropriée selon le type de fichier
            function getFileIcon($file_type) {
                $icons = [
                    'pdf' => 'far fa-file-pdf',
                    'doc' => 'far fa-file-word',
                    'docx' => 'far fa-file-word',
                    'xls' => 'far fa-file-excel',
                    'xlsx' => 'far fa-file-excel',
                    'ppt' => 'far fa-file-powerpoint',
                    'pptx' => 'far fa-file-powerpoint',
                    'jpg' => 'far fa-file-image',
                    'jpeg' => 'far fa-file-image',
                    'png' => 'far fa-file-image',
                    'txt' => 'far fa-file-alt',
                    'zip' => 'far fa-file-archive',
                    'rar' => 'far fa-file-archive'
                ];
                
                return $icons[strtolower($file_type)] ?? 'far fa-file';
            }
            
            // Fonction pour formater la taille du fichier (version PHP pour la base de données)
            function formatFileSizeDB($bytes) {
                if ($bytes === 0) return '0 Bytes';
                $k = 1024;
                $sizes = ['Bytes', 'KB', 'MB', 'GB'];
                $i = floor(log($bytes) / log($k));
                return round($bytes / pow($k, $i), 2) . ' ' . $sizes[$i];
            }
            ?>
        </div>
    </div>
    
    <script>
        // Script pour le menu burger
        const burgerMenu = document.getElementById('burger-menu');
        const navLinks = document.getElementById('nav-links');
        
        if (burgerMenu && navLinks) {
            burgerMenu.addEventListener('click', () => {
                navLinks.classList.toggle('active');
                burgerMenu.classList.toggle('active');
            });
        }
        
        // Afficher le nom du fichier sélectionné
        document.getElementById('fichier').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'Aucun fichier sélectionné';
            const fileSize = e.target.files[0] ? ' (' + formatFileSize(e.target.files[0].size) + ')' : '';
            this.nextElementSibling.textContent = 'Fichier sélectionné : ' + fileName + fileSize;
        });
        
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }
        
        // Fonction de confirmation de suppression
        function confirmDelete(fileId, fileName) {
            if (confirm('Êtes-vous sûr de vouloir supprimer le fichier "' + fileName + '" ? Cette action est irréversible.')) {
                window.location.href = '?delete=' + fileId;
            }
            return false;
        }
    </script>
</body>
</html>

<?php
// Fermer la connexion
mysqli_close($conn);
?>
