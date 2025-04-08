<?php
// Conexão com o banco de dados Railway
$conn = new mysqli(
    "shuttle.proxy.rlwy.net", 
    "root", 
    "QdSSmRjTfKJfSRTiUcgYQaAvXfRoseOc", 
    "railway", 
    39406
);

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

// Captura a categoria e a busca
$categoriaSelecionada = isset($_GET['categoria']) ? $_GET['categoria'] : '';
$busca = isset($_GET['busca']) ? $conn->real_escape_string($_GET['busca']) : '';

// Consulta os manuais com filtro de categoria e busca
$sql = "SELECT * FROM manuais WHERE 1";

if ($categoriaSelecionada !== '') {
    if ($categoriaSelecionada === 'operações') {
        $sql .= " AND categoria IN ('infantaria', 'cavalaria', 'artilharia', 'engenharia', 'comunicacoes')";
    } else {
        $sql .= " AND categoria = '$categoriaSelecionada'";
    }
}

if ($busca !== '') {
    $sql .= " AND (nome LIKE '%$busca%' OR numero LIKE '%$busca%')";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Galeria de Manuais</title>
    <style>
    body {
        
        background-color: #f1f5ec;
        margin: 0;
        padding-top: 180px; /* espaço para o cabeçalho fixo */
    }

    header {
        background-color:rgb(0, 0, 0);
        color: white;
        padding: 0px;
        text-align: center;
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1000;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
    }

    header .header-container {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
    }

    header img {
        height: 90px;
        width: auto;
        padding: 10px;
    }

    header .titulo {
        flex: 1;
        text-align: center;
        font-size: 28px;
    }

    header .titulo strong {
        display: block;
        font-size: 28px;
    }

    .menu-categorias {
        position: fixed;
        top: 105px;
        width: 100%;
        background-color:#134400;
        padding: 10px 0;
        z-index: 999;
        
        white-space: nowrap;
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        box-shadow: 0 2px 6px rgba(0,0,0,0.2);
    }

    .menu-categorias a,
    .menu-categorias .dropbtn {
        background-color: #134400;
        color: #fff;
        padding: 10px 14px;
        margin: 5px;
        border-radius: 5px;
        text-decoration: none;
        font-weight: bold;
        transition: background 0.3s;
        cursor: pointer;
        display: inline-block;
    }

    .menu-categorias a:hover,
    .menu-categorias .dropbtn:hover,
    .menu-categorias .ativo {
        background-color:#1d7702;
    }

    .dropdown {
        position: relative;
        z-index: 1000; /* mantém o botão com prioridade */
    }

    .dropdown-content {
        display: none;
        position: absolute;
        background-color: #6b8e23;
        min-width: 180px;
        z-index: 2000; /* AQUI É O PONTO-CHAVE! */
        border-radius: 6px;
        top: 100%; /* garante que o submenu apareça abaixo do botão */
        left: 0;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.3);
    }

    .dropdown:hover .dropdown-content {
        display: block;
    }

    .dropdown-content a {
        color: white;
        padding: 10px;
        display: block;
        border-bottom: 1px solid #4f651d;
        text-align: left;
    }

    .busca {
        text-align: center;
        margin: 50px 0 30px;
    }

    .busca input[type="text"] {
        padding: 10px;
        width: 50%;
        max-width: 400px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    .busca input[type="submit"] {
        padding: 10px 16px;
        background-color: #556b2f;
        border: none;
        color: #fff;
        border-radius: 5px;
        cursor: pointer;
        margin-left: 5px;
    }

    .busca input[type="submit"]:hover {
        background-color: #3e5223;
    }

    .galeria {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 25px;
        padding: 0 20px;
        position: relative; /* ok! */
        z-index: 1; /* mantenha isso baixo */
    }

    .manual {
        background-color: #fff;
        border: 1px solid #ccc;
        border-radius: 10px;
        padding: 15px;
        text-align: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    .manual img {
        width: 100%;
        height: 420px;
        object-fit: cover;
        border-radius: 8px;
        cursor: pointer;
        transition: filter 0.3s ease;
    }

    .manual img:hover {
        filter: brightness(70%);
    }

    .manual h3 {
        font-size: 16px;
        margin: 10px 0 5px;
    }

    .manual .icons {
        margin-top: 8px;
        
        
    }

    .manual .icons a {
        margin: 0 5px;
        color: #556b2f;
        font-size: 20px;
        text-decoration: none;
     
    }

    .manual .icons a:hover {
        color: #3e5223;
    }

    #pdfViewerContainer {
        display: none;
        padding: 20px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 0 10px rgba(0,0,0,0.2);
        margin: 40px 20px 0;
    }

    #pdfViewerContainer iframe {
        width: 100%;
        height: 700px;
        border: none;
    }

    #pdfViewerContainer button {
        padding: 10px 15px;
        background-color: #8b0000;
        color: white;
        border: none;
        border-radius: 5px;
        margin-bottom: 10px;
        cursor: pointer;
    }

    #pdfViewerContainer button:hover {
        background-color: #a50000;
    }

    @media (max-width: 768px) {
        header .titulo {
            font-size: 14px;
        }

        header .titulo strong {
            font-size: 18px;
        }

        .manual img {
            height: 350px;
        }
    }

    @media (max-width: 500px) {
        header {
            padding: 10px;
        }

        .menu-categorias {
            z-index: 1500;
            flex-direction: column;
            align-items: center;
        }

        .busca input[type="text"] {
            width: 90%;
        }

        .galeria {
            padding: 0 10px;
        }
    }
    </style>
</head>
<body>

<header>
    <div class="header-container">
        <img src="img/logo1.png" alt="Logo Esquerda">
        <div class="titulo">
            <strong>CENTRO DE DOUTRINA DO EXÉRCITO</strong><br>PAINEL DE MANUAIS
        </div>
        <img src="img/logo2.png" alt="Logo Direita">
    </div>
</header>

<div class="menu-categorias">
    <a href="index.php" class="<?= $categoriaSelecionada === '' ? 'ativo' : '' ?>">TODOS</a>
    <a href="index.php?categoria=pessoal" class="<?= $categoriaSelecionada === 'pessoal' ? 'ativo' : '' ?>">1 - PESSOAL</a>
    <a href="index.php?categoria=inteligência" class="<?= $categoriaSelecionada === 'inteligência' ? 'ativo' : '' ?>">2 - INTELIGÊNCIA</a>

    <div class="dropdown">
        <span class="dropbtn <?= in_array($categoriaSelecionada, ['operações','infantaria','cavalaria','artilharia','engenharia','comunicacoes']) ? 'ativo' : '' ?>">3 - OPERAÇÕES ▾</span>
        <div class="dropdown-content">
            <a href="index.php?categoria=operações">TODOS OPERAÇÕES</a>
            <a href="index.php?categoria=infantaria">INFANTARIA</a>
            <a href="index.php?categoria=cavalaria">CAVALARIA</a>
            <a href="index.php?categoria=artilharia">ARTILHARIA</a>
            <a href="index.php?categoria=engenharia">ENGENHARIA</a>
            <a href="index.php?categoria=comunicacoes">COMUNICAÇÕES</a>
        </div>
    </div>

    <a href="index.php?categoria=logística" class="<?= $categoriaSelecionada === 'logística' ? 'ativo' : '' ?>">4 - LOGÍSTICA</a>
    <a href="index.php?categoria=processos" class="<?= $categoriaSelecionada === 'processos' ? 'ativo' : '' ?>">5 - PROCESSOS</a>
    <a href="index.php?categoria=comando e controle" class="<?= $categoriaSelecionada === 'comando e controle' ? 'ativo' : '' ?>">6 - Cmdo Ctrl (C2)</a>
    <a href="index.php?categoria=geral" class="<?= $categoriaSelecionada === 'geral' ? 'ativo' : '' ?>">7 - GERAL</a>
    <a href="index.php?categoria=manuais técnicos" class="<?= $categoriaSelecionada === 'manuais técnicos' ? 'ativo' : '' ?>">8 - MANUAIS TÉCNICOS</a>
    <a href="index.php?categoria=notas doutrinarias" class="<?= $categoriaSelecionada === 'notas doutrinarias' ? 'ativo' : '' ?>">9 - NOTAS DOUTRINÁRIAS</a>
</div>

<div class="busca">
    <form method="get" action="index.php">
        <input type="text" name="busca" placeholder="Buscar por nome do manual..." value="<?= isset($_GET['busca']) ? htmlspecialchars($_GET['busca']) : '' ?>">
        <input type="submit" value="Buscar">
    </form>
</div>

<div class="galeria">
    <?php while ($row = $result->fetch_assoc()): ?>
        <div class="manual">
            <img src="uploads/capas/<?= htmlspecialchars($row['capa']) ?>" alt="Capa do Manual" onclick="abrirPDF('uploads/pdf/<?= htmlspecialchars($row['pdf']) ?>')">
            <h3><?= htmlspecialchars($row['numero']) ?> - <?= htmlspecialchars($row['nome']) ?></h3>
            <div class="icons">
                <?php if (!empty($row['video'])): ?>
                    <a href="uploads/videos/<?= htmlspecialchars($row['video']) ?>" target="_blank" title="Ver vídeo 📹">📹</a>
                <?php endif; ?>
                <?php if (!empty($row['epub'])): ?>
                    <a href="uploads/epub/<?= htmlspecialchars($row['epub']) ?>" download title="Baixar EPUB 📘">📘</a>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<div id="pdfViewerContainer">
    <button onclick="fecharPDF()">✖ Fechar PDF</button>
    <iframe id="visualizador"></iframe>
</div>

<script>
    function abrirPDF(pdfPath) {
        const frame = document.getElementById("visualizador");
        const container = document.getElementById("pdfViewerContainer");

        frame.src = pdfPath;
        container.style.display = "block";

        window.scrollTo({
            top: container.offsetTop,
            behavior: 'smooth'
        });
    }

    function fecharPDF() {
        const frame = document.getElementById("visualizador");
        const container = document.getElementById("pdfViewerContainer");

        frame.src = "";
        container.style.display = "none";

        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
</script>

</body>
</html>
