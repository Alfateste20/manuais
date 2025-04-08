<?php
$conn = new mysqli("localhost", "root", "", "painel_manuais");
if ($conn->connect_error) die("Erro: " . $conn->connect_error);

// Excluir manual, se solicitado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir_id'])) {
    $id = (int) $_POST['excluir_id'];

    // Buscar nomes dos arquivos antes de excluir do banco
    $busca = $conn->prepare("SELECT capa, pdf, epub, video FROM manuais WHERE id = ?");
    $busca->bind_param("i", $id);
    $busca->execute();
    $res = $busca->get_result();
    $arqs = $res->fetch_assoc();

    if ($arqs) {
        @unlink("uploads/capas/" . $arqs['capa']);
        @unlink("uploads/pdf/" . $arqs['pdf']);
        @unlink("uploads/epub/" . $arqs['epub']);
        @unlink("uploads/videos/" . $arqs['video']);

        $del = $conn->prepare("DELETE FROM manuais WHERE id = ?");
        $del->bind_param("i", $id);
        $del->execute();

        echo "<script>alert('Manual excluído com sucesso!'); window.location.href='editar.php';</script>";
        exit;
    }
}

// Filtros
$ano = $_GET['ano'] ?? '';
$categoria = $_GET['categoria'] ?? '';

$sql = "SELECT id, numero, nome, ano, categoria FROM manuais WHERE 1=1";
$params = [];
$types = "";

if (!empty($ano)) {
    $sql .= " AND ano = ?";
    $params[] = $ano;
    $types .= "s";
}

if (!empty($categoria)) {
    $sql .= " AND categoria = ?";
    $params[] = $categoria;
    $types .= "s";
}

$sql .= " ORDER BY id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Manuais</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f6f2;
            padding: 40px;
        }

        h1 {
            text-align: center;
            color: #2e4d2e;
        }

        .top-bar {
            display: flex;
            justify-content: flex-end;
            margin: 20px 0;
        }

        .btn-cadastrar {
            background-color: #4d6f3c;
            color: white;
            padding: 10px 18px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            text-decoration: none;
            font-size: 15px;
            transition: background 0.3s;
        }

        .btn-cadastrar:hover {
            background-color: #365028;
        }

        form.filtros {
            margin-bottom: 20px;
        }

        form.filtros input {
            padding: 6px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        form.filtros button {
            padding: 6px 12px;
            background-color: #3e5223;
            color: white;
            border: none;
            border-radius: 4px;
            font-weight: bold;
            cursor: pointer;
        }

        form.filtros button:hover {
            background-color: #2c3e1e;
        }

        form.filtros a {
            margin-left: 8px;
            text-decoration: none;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            background-color: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 14px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #3e5223;
            color: white;
        }

        tr:hover {
            background-color: #f2f2f2;
        }

        .btn-editar, .btn-excluir {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            text-decoration: none;
            cursor: pointer;
        }

        .btn-editar {
            background-color: #3e5223;
            color: white;
            margin-right: 6px;
        }

        .btn-editar:hover {
            background-color: #2c3e1e;
        }

        .btn-excluir {
            background-color: #a52828;
            color: white;
        }

        .btn-excluir:hover {
            background-color: #7a1d1d;
        }

        form.inline {
            display: inline;
        }
    </style>
</head>
<body>
    <h1>Editar Manuais Cadastrados</h1>

    <div class="top-bar">
        <a href="upload.php" class="btn-cadastrar">Cadastrar Manual</a>
    </div>

    <form method="GET" class="filtros">
        <label for="ano">Ano:</label>
        <input type="text" id="ano" name="ano" value="<?= htmlspecialchars($ano) ?>" placeholder="Ex: 2023">

        <label for="categoria" style="margin-left: 10px;">Categoria:</label>
        <input type="text" id="categoria" name="categoria" value="<?= htmlspecialchars($categoria) ?>" placeholder="Ex: Infantaria">

        <button type="submit">Filtrar</button>
        <a href="editar.php">Limpar</a>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Numeração</th>
                <th>Nome</th>
                <th>Ano</th>
                <th>Categoria</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $row['id'] ?></td>
                    <td><?= htmlspecialchars($row['numero']) ?></td>
                    <td><?= htmlspecialchars($row['nome']) ?></td>
                    <td><?= htmlspecialchars($row['ano']) ?></td>
                    <td><?= htmlspecialchars($row['categoria']) ?></td>
                    <td>
                        <a class="btn-editar" href="editar_manual.php?id=<?= $row['id'] ?>">Editar</a>
                        <form method="post" class="inline" onsubmit="return confirm('Tem certeza que deseja excluir este manual?');">
                            <input type="hidden" name="excluir_id" value="<?= $row['id'] ?>">
                            <button type="submit" class="btn-excluir">Excluir</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>
