<?php
$conn = new mysqli("localhost", "root", "", "painel_manuais");
if ($conn->connect_error) die("Erro: " . $conn->connect_error);

$id = $_GET['id'] ?? null;
if (!$id) die("ID do manual não informado.");

// Buscar dados do manual
$stmt = $conn->prepare("SELECT * FROM manuais WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$manual = $result->fetch_assoc();
if (!$manual) die("Manual não encontrado.");

// EXCLUSÃO
if (isset($_POST['excluir'])) {
    // Exclui arquivos fisicamente (opcional)
    @unlink("uploads/capas/" . $manual['capa']);
    @unlink("uploads/pdf/" . $manual['pdf']);
    @unlink("uploads/epub/" . $manual['epub']);
    @unlink("uploads/videos/" . $manual['video']);

    $stmt = $conn->prepare("DELETE FROM manuais WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: editar.php?msg=manual_excluido");
    exit;
}

// ATUALIZAÇÃO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['excluir'])) {
    $numero = $_POST['numero'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $edicao = $_POST['edicao'];
    $ano = $_POST['ano'];
    $categoria = $_POST['categoria'];

    $capa = $_FILES['capa']['name'] ? $_FILES['capa']['name'] : $manual['capa'];
    $pdf = $_FILES['pdf']['name'] ? $_FILES['pdf']['name'] : $manual['pdf'];
    $epub = $_FILES['epub']['name'] ? $_FILES['epub']['name'] : $manual['epub'];
    $video = $_FILES['video']['name'] ? $_FILES['video']['name'] : $manual['video'];

    if ($_FILES['capa']['name']) move_uploaded_file($_FILES['capa']['tmp_name'], "uploads/capas/$capa");
    if ($_FILES['pdf']['name']) move_uploaded_file($_FILES['pdf']['tmp_name'], "uploads/pdf/$pdf");
    if ($_FILES['epub']['name']) move_uploaded_file($_FILES['epub']['tmp_name'], "uploads/epub/$epub");
    if ($_FILES['video']['name']) move_uploaded_file($_FILES['video']['tmp_name'], "uploads/videos/$video");

    $stmt = $conn->prepare("UPDATE manuais SET numero=?, nome=?, descricao=?, edicao=?, ano=?, categoria=?, capa=?, pdf=?, epub=?, video=? WHERE id=?");
    $stmt->bind_param("ssssisssssi", $numero, $nome, $descricao, $edicao, $ano, $categoria, $capa, $pdf, $epub, $video, $id);
    $stmt->execute();

    echo "<div class='popup'>Manual atualizado com sucesso!</div>";

    header("Location: editar.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Manual</title>
    <style>
        body {
            font-family: "Segoe UI", sans-serif;
            background-color: #f4f4f4;
            padding: 30px;
        }

        h1 {
            color: #2e4d2e;
            text-align: center;
        }

        form {
            max-width: 700px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0px 0px 10px rgba(0,0,0,0.1);
            border-left: 8px solid #556b2f;
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #2e4d2e;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            border: 2px solid #ccc;
            border-radius: 6px;
            margin-top: 5px;
            font-size: 14px;
        }

        textarea {
            resize: vertical;
        }

        input[type="file"] {
            margin-top: 5px;
        }

        .btn {
            margin-top: 25px;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }

        .btn-editar {
            background-color: #556b2f;
            color: white;
        }

        .btn-editar:hover {
            background-color: #3e5223;
        }

        .btn-excluir {
            background-color: #a52828;
            color: white;
        }

        .btn-excluir:hover {
            background-color: #7a1d1d;
        }

        .popup {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: 0 0 12px rgba(0,0,0,0.2);
            font-weight: bold;
            z-index: 999;
        }
    </style>
</head>
<body>

<h1>Editar Manual</h1>

<form method="post" enctype="multipart/form-data" onsubmit="return confirmExclusao(this)">
    <label>Numeração:</label>
    <input type="text" name="numero" value="<?= htmlspecialchars($manual['numero']) ?>" required>

    <label>Nome:</label>
    <input type="text" name="nome" value="<?= htmlspecialchars($manual['nome']) ?>" required>

    <label>Descrição:</label>
    <textarea name="descricao" required><?= htmlspecialchars($manual['descricao']) ?></textarea>

    <label>Edição:</label>
    <input type="text" name="edicao" value="<?= htmlspecialchars($manual['edicao']) ?>" required>

    <label>Ano:</label>
    <input type="number" name="ano" value="<?= $manual['ano'] ?>" required>

    <label>Categoria:</label>
    <select name="categoria" required>
        <option value="">Selecione...</option>
        <?php
        $categorias = [
            "pessoal", "inteligência", "operações",
            "infantaria", "cavalaria", "artilharia", "engenharia", "comunicacoes",
            "logística", "processos", "comando e controle", "geral",
            "manuais técnicos", "notas doutrinarias"
        ];
        foreach ($categorias as $cat) {
            $selected = ($manual['categoria'] === $cat) ? "selected" : "";
            echo "<option value='$cat' $selected>$cat</option>";
        }
        ?>
    </select>

    <label>Nova Capa (deixe em branco para manter):</label>
    <input type="file" name="capa">

    <label>Novo PDF (deixe em branco para manter):</label>
    <input type="file" name="pdf">

    <label>Novo EPUB (deixe em branco para manter):</label>
    <input type="file" name="epub">

    <label>Novo Vídeo (deixe em branco para manter):</label>
    <input type="file" name="video" accept="video/mp4">

    <button type="submit" class="btn btn-editar">Salvar Alterações</button>

    <button type="submit" name="excluir" class="btn btn-excluir">Excluir Manual</button>
</form>

<script>
function confirmExclusao(form) {
    if (form.querySelector('[name="excluir"]')?.clicked) {
        return confirm("Tem certeza que deseja excluir este manual?");
    }
    return true;
}

// Detecta qual botão foi clicado
document.querySelectorAll("button").forEach(btn => {
    btn.addEventListener("click", function() {
        this.form.querySelectorAll("button").forEach(b => b.clicked = false);
        this.clicked = true;
    });
});
</script>

</body>
</html>
