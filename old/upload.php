<?php
$conn = new mysqli("localhost", "root", "", "painel_manuais");
if ($conn->connect_error) die("Erro: " . $conn->connect_error);

$mensagemSucesso = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $numero = $_POST['numero'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $edicao = $_POST['edicao'];
    $ano = $_POST['ano'];
    $categoria = $_POST['categoria'];
    $capa = $_FILES['capa']['name'];
    $pdf = $_FILES['pdf']['name'];
    $epub = $_FILES['epub']['name'];
    $video = $_FILES['video']['name'];

    move_uploaded_file($_FILES['video']['tmp_name'], "uploads/videos/$video");
    move_uploaded_file($_FILES['capa']['tmp_name'], "uploads/capas/$capa");
    move_uploaded_file($_FILES['pdf']['tmp_name'], "uploads/pdf/$pdf");
    move_uploaded_file($_FILES['epub']['tmp_name'], "uploads/epub/$epub");

    $stmt = $conn->prepare("INSERT INTO manuais (numero, nome, descricao, edicao, ano, categoria, capa, pdf, epub, video)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssisssss", $numero, $nome, $descricao, $edicao, $ano, $categoria, $capa, $pdf, $epub, $video);

    $stmt->execute();

    $mensagemSucesso = true;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro de Manual</title>
    <style>
        body {
            background-color: #e9f1e5;
            font-family: "Segoe UI", sans-serif;
            padding: 40px 20px;
        }

        h1 {
            color: #2e4d2e;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
        }

        form {
            max-width: 700px;
            margin: auto;
            background: #fff;
            padding: 30px 35px;
            border-radius: 16px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.1);
            border-top: 10px solid #3e5223;
        }

        label {
            display: block;
            margin-top: 18px;
            color: #2e4d2e;
            font-weight: bold;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 12px;
            border: 1.5px solid #ccc;
            border-radius: 8px;
            margin-top: 6px;
            font-size: 15px;
            background-color: #fdfdfd;
        }

        textarea {
            resize: vertical;
            min-height: 90px;
        }

        input[type="file"] {
            margin-top: 6px;
            font-size: 14px;
        }

        button {
            margin-top: 25px;
            width: 100%;
            background-color: #3e5223;
            color: #fff;
            padding: 14px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        button:hover {
            background-color: #2d3b1a;
        }

        select option {
            padding-left: 8px;
        }

        /* Modal de sucesso */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background-color: rgba(0, 0, 0, 0.4);
            z-index: 999;
            justify-content: center;
            align-items: center;
        }

        .modal-content {
            background-color: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
            padding: 30px;
            border-radius: 12px;
            text-align: center;
            font-size: 18px;
            max-width: 400px;
            box-shadow: 0 0 12px rgba(0,0,0,0.2);
        }

        .modal-content button {
            margin-top: 20px;
            background-color: #3e5223;
            padding: 10px 20px;
            border: none;
            color: white;
            border-radius: 6px;
            font-size: 15px;
            cursor: pointer;
        }

        .modal-content button:hover {
            background-color: #2d3b1a;
        }
    </style>
</head>
<body>

<h1>Cadastrar Manual</h1>
<form method="post" enctype="multipart/form-data">
    <label>Numeração:</label>
    <input type="text" name="numero" required>

    <label>Nome do Manual:</label>
    <input type="text" name="nome" required>

    <label>Descrição:</label>
    <textarea name="descricao" required></textarea>

    <label>Edição:</label>
    <input type="text" name="edicao" required>

    <label>Ano:</label>
    <input type="number" name="ano" required>

    <label for="categoria">Categoria:</label>
    <select name="categoria" id="categoria" required>
        <option value="">Selecione...</option>
        <option value="pessoal">Pessoal</option>
        <option value="inteligência">Inteligência</option>
        <option value="operações">Operações</option>
        <option value="infantaria">↳ Infantaria</option>
        <option value="cavalaria">↳ Cavalaria</option>
        <option value="artilharia">↳ Artilharia</option>
        <option value="engenharia">↳ Engenharia</option>
        <option value="comunicacoes">↳ Comunicações</option>
        <option value="logística">Logística</option>
        <option value="processos">Processos</option>
        <option value="comando e controle">Comando e Controle</option>
        <option value="geral">Geral</option>
        <option value="manuais técnicos">Manuais Técnicos</option>
        <option value="notas doutrinarias">Notas Doutrinárias</option>
    </select>

    <label>Arquivo de Vídeo (.mp4):</label>
    <input type="file" name="video" accept="video/mp4">

    <label>Imagem da Capa:</label>
    <input type="file" name="capa" required>

    <label>Arquivo PDF:</label>
    <input type="file" name="pdf" required>

    <label>Arquivo EPUB:</label>
    <input type="file" name="epub">

    <button type="submit">Cadastrar Manual</button>
   
</form>
<a href="editar.php"><button>Manuais Cadastrados</button></a>
<!-- Modal de Sucesso -->
<div class="modal-overlay" id="modalSucesso">
    <div class="modal-content">
        Manual cadastrado com sucesso!
        <br>
        <button onclick="fecharModal()">OK</button>
    </div>
</div>


<script>
    function fecharModal() {
        document.getElementById("modalSucesso").style.display = "none";
    }

    // Mostrar modal se houve sucesso no cadastro
    <?php if ($mensagemSucesso): ?>
        document.getElementById("modalSucesso").style.display = "flex";
    <?php endif; ?>
</script>

</body>
</html>
