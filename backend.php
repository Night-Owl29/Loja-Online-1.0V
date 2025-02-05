<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "loja_online";

$conn = new mysqli($host, $user, $password, $dbname);

if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

$method = $_SERVER['REQUEST_METHOD'];

// Adicionar Produto
if ($method === 'POST') {
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];

    $imagem = '';
    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'Uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $imagemNome = uniqid() . '_' . basename($_FILES['imagem']['name']);
        $imagemCaminho = $uploadDir . $imagemNome;
        if (move_uploaded_file($_FILES['imagem']['tmp_name'], $imagemCaminho)) {
            $imagem = $imagemCaminho;
        }
    }

    $sql = "INSERT INTO produtos (titulo, descricao, preco, imagem) VALUES ('$titulo', '$descricao', '$preco', '$imagem')";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['mensagem' => 'Produto adicionado com sucesso!']);
    } else {
        echo json_encode(['mensagem' => 'Erro: ' . $conn->error]);
    }
    exit;
}

// Listar Produtos
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'listar') {
    $result = $conn->query("SELECT * FROM produtos ORDER BY id DESC");
    $produtos = [];

    while ($row = $result->fetch_assoc()) {
        $produtos[] = $row;
    }

    echo json_encode($produtos);
    exit;
}

// Excluir Produto
if ($method === 'DELETE' && isset($_GET['id'])) {
    $id = intval($_GET['id']);

    $result = $conn->query("SELECT imagem FROM produtos WHERE id = $id");
    if ($result && $row = $result->fetch_assoc()) {
        if (file_exists($row['imagem'])) {
            unlink($row['imagem']);
        }
    }

    $sql = "DELETE FROM produtos WHERE id = $id";
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['mensagem' => 'Produto excluído com sucesso!']);
    } else {
        echo json_encode(['mensagem' => 'Erro ao excluir o produto: ' . $conn->error]);
    }
    exit;
}

$conn->close();
?>