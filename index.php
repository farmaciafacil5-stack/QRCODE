<?php
$pixGerado = false;
$pixBase64 = "";
$copiarCodigo = "";
$valor = "";
$nome = "";
$email = "";
$cpf = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $valor = floatval($_POST['valor']);
    
    // Gerar dados fictícios
    $nome = "Cliente Teste " . rand(100,999);
    $email = "cliente" . rand(100,999) . "@gmail.com";
    $cpf = str_pad(rand(10000000000, 99999999999), 11, "0", STR_PAD_LEFT);

    $PUBLIC_KEY = "silvasilvabussines_eqrl5kh2i8qmwbf2";
    $SECRET_KEY = "17dxpuct4yi2tovtaabimsyro7r8wl76sii1m9i3kplw9lybfgmt97bqynsy0v8b";
    $API_URL = "https://app.sigilopay.com.br/api/v1/gateway/pix/receive";

    $body = [
        "identifier" => uniqid(),
        "amount" => $valor,
        "client" => [
            "name" => $nome,
            "email" => $email,
            "phone" => "(11) 99999-9999",
            "document" => "32614241415",
        ],
        "products" => [
            [
                "id" => "1",
                "name" => "Produto de Teste",
                "quantity" => 1,
                "price" => $valor
            ]
        ],
        "dueDate" => date("Y-m-d", strtotime("+1 day")),
        "callbackUrl" => "https://seusite.com.br/callback-pix"
    ];

    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => $API_URL,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "x-public-key: $PUBLIC_KEY",
            "x-secret-key: $SECRET_KEY"
        ]
    ]);

    $response = curl_exec($curl);
    $data = json_decode($response, true);

    if(isset($data['pix']['base64']) && !empty($data['pix']['base64'])){
        // Garantir que o base64 tenha o prefixo correto
        if(strpos($data['pix']['base64'], 'data:image/png;base64,') !== 0){
            $pixBase64 = 'data:image/png;base64,' . $data['pix']['base64'];
        } else {
            $pixBase64 = $data['pix']['base64'];
        }
        $copiarCodigo = $data['pix']['code'];
        $pixGerado = true;
    } else {
        echo "<p style='color:red;'>❌ Não foi possível gerar o QR Code. Verifique a API.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<title>💳 Gerador de PIX</title>
<style>
body {
    margin:0;
    padding:0;
    font-family: 'Orbitron', sans-serif;
    background: #000;
    color: #00ffff;
    display:flex;
    justify-content:center;
    align-items:flex-start;
    min-height:100vh;
}
.container {
    margin-top:50px;
    background: #0a0a0a;
    padding: 40px 50px;
    border-radius: 15px;
    box-shadow: 0 0 40px #00ffff;
    width: 400px;
    text-align: center;
}
h1 {
    color:#00ffff;
    margin-bottom:25px;
    font-size:28px;
}
label {
    display:block;
    margin-bottom:8px;
    font-weight:bold;
    text-align:left;
}
input {
    width:100%;
    padding:12px;
    margin-bottom:20px;
    border-radius:10px;
    border:none;
    font-size:16px;
    text-align:center;
    background:#111;
    color:#00f0ff;
}
button {
    width:100%;
    padding:14px;
    border-radius:10px;
    border:none;
    background:#00f0ff;
    color:#000;
    font-weight:bold;
    font-size:18px;
    cursor:pointer;
}
.qr-container {
    margin-top:30px;
}
.qr-container img {
    width:220px;
    height:220px;
    border-radius:15px;
    box-shadow:0 0 30px #00f0ff;
}
.codigo-pix {
    background:#111;
    padding:12px;
    margin-top:15px;
    border-radius:10px;
    word-break: break-all;
    color:#00f0ff;
    cursor:pointer;
}
.info {
    text-align:left;
    margin-top:20px;
    color:#00e5ff;
    font-size:15px;
}
</style>
</head>
<body>
<div class="container">
    <h1>💸 Gerar PIX</h1>
    <form method="POST">
        <label for="valor">Digite o valor (R$)</label>
        <input type="number" step="0.01" name="valor" id="valor" placeholder="Ex: 50.00" required>
        <button type="submit">Gerar PIX</button>
    </form>

    <?php if($pixGerado): ?>
        <div class="qr-container">
            <img src="<?= $pixBase64 ?>" alt="QR Code PIX"/>
            <div class="codigo-pix" onclick="navigator.clipboard.writeText('<?= $copiarCodigo ?>'); alert('Código PIX copiado!');">
                <?= $copiarCodigo ?>
            </div>
            <div class="info">
                <p><strong>Valor:</strong> R$ <?= number_format($valor,2,",",".") ?></p>
            </div>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
