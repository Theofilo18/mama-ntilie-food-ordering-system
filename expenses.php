<?php
require_once 'db.php'; require_role('vendor'); $vendor_id=(int)$_SESSION['user_id']; $error='';
if($_SERVER['REQUEST_METHOD']==='POST') {
    verify_csrf(); $action=$_POST['action']??'';
    if($action==='add') {
        $description=trim($_POST['description']??''); $amount=filter_input(INPUT_POST,'amount',FILTER_VALIDATE_FLOAT);
        if($description==='' || $amount===false || $amount<=0) $error='Enter a description and an amount greater than zero.';
        else { $stmt=$conn->prepare('INSERT INTO expenses (vendor_id,description,amount) VALUES (?,?,?)'); $stmt->bind_param('isd',$vendor_id,$description,$amount); $stmt->execute(); set_flash('success','Expense recorded.'); redirect('expenses.php'); }
    } elseif($action==='delete') {
        $expense_id=(int)($_POST['expense_id']??0); $stmt=$conn->prepare('DELETE FROM expenses WHERE id=? AND vendor_id=?'); $stmt->bind_param('ii',$expense_id,$vendor_id); $stmt->execute(); set_flash('success','Expense deleted.'); redirect('expenses.php');
    }
}
$stmt=$conn->prepare('SELECT id,description,amount,created_at FROM expenses WHERE vendor_id=? ORDER BY created_at DESC'); $stmt->bind_param('i',$vendor_id); $stmt->execute(); $expenses=$stmt->get_result(); $flash=get_flash();
?>
<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Expenses | Mama Ntilie</title><link rel="stylesheet" href="style.css"></head><body><header class="topbar"><a class="brand" href="vendor_dashboard.php">🍲 Mama Ntilie</a><a href="logout.php">Sign out</a></header><main class="page-wrap"><a class="back-link" href="vendor_dashboard.php">← Dashboard</a><span class="eyebrow">KNOW YOUR COSTS</span><h1>Business expenses</h1><?php if($flash): ?><div class="alert success"><?= e($flash['message']) ?></div><?php endif; ?><?php if($error): ?><div class="alert error"><?= e($error) ?></div><?php endif; ?>
<section class="panel"><h2>Record an expense</h2><form method="post" class="form-grid"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="add"><label>Description<input name="description" placeholder="e.g. Rice, cooking oil or charcoal" required></label><label>Amount (TZS)<input type="number" name="amount" min="1" step="1" required></label><button class="btn btn-primary full">Save expense</button></form></section>
<h2 class="section-heading">Expense history</h2><div class="table-wrap"><table><thead><tr><th>Description</th><th>Amount</th><th>Date</th><th></th></tr></thead><tbody><?php if($expenses->num_rows): while($ex=$expenses->fetch_assoc()): ?><tr><td><?= e($ex['description']) ?></td><td><?= money($ex['amount']) ?></td><td><?= e($ex['created_at']) ?></td><td><form method="post" onsubmit="return confirm('Delete this expense?');"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="expense_id" value="<?= (int)$ex['id'] ?>"><button class="btn btn-small btn-danger">Delete</button></form></td></tr><?php endwhile; else: ?><tr><td colspan="4">No expenses recorded yet.</td></tr><?php endif; ?></tbody></table></div></main></body></html>
