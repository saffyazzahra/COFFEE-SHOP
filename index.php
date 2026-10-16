<?php
// index.php
include 'auth.php';
include 'config.php';

$user_id = $_SESSION['user_id'] ?? null;
$role = $_SESSION['role'] ?? 'guest';

// Handle the "continue as guest" choice
if (isset($_GET['guest'])) {
    $_SESSION['guest_choice'] = true;
    header("Location: index.php");
    exit;
}

// Not logged in and hasn't chosen guest yet? Show the welcome gate instead of the menu.
if (!isset($_SESSION['user_id']) && !isset($_SESSION['guest_choice'])) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Welcome - Coffee Order</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
    <div class="container">
        <div class="row justify-content-center" style="margin-top: 15vh;">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-dark text-white text-center">
                        <h5 class="mb-0">Welcome to Coffee Order</h5>
                    </div>
                    <div class="card-body text-center">
                        <p class="text-muted mb-4">Log in for 10% off your order, or just browse the menu.</p>
                        <div class="d-grid gap-2">
                            <a href="login.php" class="btn btn-dark">Login</a>
                            <a href="register.php" class="btn btn-outline-dark">Register</a>
                        </div>
                        <a href="index.php?guest=1" class="btn btn-link btn-sm text-muted mt-3">Maybe next time &mdash; continue as guest</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </body>
    </html>
    <?php
    exit;
}

// Initialize session-based ordered array if it doesn't exist
if (!isset($_SESSION['ordered_products'])) {
    $_SESSION['ordered_products'] = [];
}

//order action when clicked
if (isset($_GET['order_id']) && $role != 'admin') {
    $product_id = intval($_GET['order_id']);
    
    // Add product ID to session array if not already there
    if (!in_array($product_id, $_SESSION['ordered_products'])) {
        $_SESSION['ordered_products'][] = $product_id;
    }

    header("Location: index.php");
    exit();
}

$query = "SELECT s.id, s.name, s.price, s.url_image FROM product s";
$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coffee Menu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>
    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Coffee Menu</h4>
                <?php if ($role == 'admin'): ?>
                    <a href="create.php" class="btn btn-success btn-sm">+ Add Product</a>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>No</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Price</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($result)): 
                                $prod_id = $row['id'];
                                $is_ordered = in_array($prod_id, $_SESSION['ordered_products']);
                            ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td><img src="<?= htmlspecialchars($row['url_image']) ?>" width="100" class="img-fluid" alt="coffee image"></td>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td>
                                <?php if(isset($_SESSION['user_id']) && $_SESSION['role'] == 'customer'){ ?>
                                    <strike><?= htmlspecialchars($row['price']) ?></strike><br/>
                                    <?= htmlspecialchars($row['price']*0.9) ?>
                                <?php } else{ ?>
                                    <?= htmlspecialchars($row['price']) ?>
                                <?php } ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($role == 'admin'): ?>
                                        <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
                                    <?php else: ?>
                                        <!-- Both customers and guests can order here -->
                                        <?php if ($is_ordered): ?>
                                            <span class="btn btn-secondary btn-sm disabled">Ordered</span>
                                        <?php else: ?>
                                            <a href="index.php?order_id=<?= $row['id'] ?>" class="btn btn-success btn-sm">Order</a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>