<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once('../config.php');


if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

$error = '';
$success = '';


if (isset($_POST['delete_user'])) {
    $user_id = $_POST['user_id'];
    if ($user_id != $_SESSION['user_id']) { 
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        if ($stmt->execute([$user_id])) {
            $success = "Utilisateur supprimé avec succès";
        } else {
            $error = "Erreur lors de la suppression de l'utilisateur";
        }
    } else {
        $error = "Vous ne pouvez pas supprimer votre propre compte";
    }
}


if (isset($_POST['change_role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['new_role'];
    if ($user_id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        if ($stmt->execute([$new_role, $user_id])) {
            $success = "Rôle modifié avec succès";
        } else {
            $error = "Erreur lors de la modification du rôle";
        }
    } else {
        $error = "Vous ne pouvez pas modifier votre propre rôle";
    }
}

$stmt = $pdo->query("SELECT * FROM users ORDER BY date_inscription DESC");
$users = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs - Administration</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Gestion des Utilisateurs</h1>
        
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Date d'inscription</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                    <td><?php echo htmlspecialchars($user['nom']); ?></td>
                    <td><?php echo htmlspecialchars($user['prenom']); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <select name="new_role" onchange="this.form.submit()" <?php echo $user['id'] == $_SESSION['user_id'] ? 'disabled' : ''; ?>>
                                <option value="utilisateur" <?php echo $user['role'] == 'utilisateur' ? 'selected' : ''; ?>>Utilisateur</option>
                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                            <input type="hidden" name="change_role" value="1">
                        </form>
                    </td>
                    <td><?php echo date('d/m/Y H:i', strtotime($user['date_inscription'])); ?></td>
                    <td>
                        <?php if ($user['id'] != $_SESSION['user_id']): ?>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur ?');">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <button type="submit" name="delete_user" class="btn btn-danger">Supprimer</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <p><a href="../index.php" class="btn btn-primary">Retour à l'accueil</a></p>
    </div>
</body>
</html> 