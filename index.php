<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Plataforma Automotiva Integrada - Borracharia, Autopeças e Mecânica</title>
    <link rel="stylesheet" href="css/style.css">

    <style>
        /* Estilos específicos para o fundo e legibilidade da página principal */
        html, body {
            height: 100%; /* Garante que o html e body ocupem 100% da altura da viewport */
            margin: 0;
            padding: 0;
            /* Mantive o fundo geral em style.css, este será sobreposto */
        }

        body {
            background-image: url('img/fundo_principal.jpg'); /* Caminho relativo à pasta raiz */
            background-repeat: no-repeat;
            background-size: cover; /* Cobre todo o fundo, redimensionando a imagem */
            background-position: center center; /* Centraliza a imagem */
            background-attachment: fixed; /* Mantém a imagem fixa durante a rolagem */
            display: flex; /* Para centralizar o conteúdo verticalmente */
            flex-direction: column; /* Organiza os itens em coluna */
        }

        /* Ajustes para o cabeçalho principal */
        .main-header {
            background-color: rgba(26, 26, 26, 0.85); /* Fundo semi-transparente escuro para o cabeçalho */
            color: #ffffff;
            padding: 15px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
            width: 100%; /* Garante que o cabeçalho ocupe toda a largura */
        }
        .main-header .container {
            max-width: 1200px; /* Aumenta a largura do container */
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0 20px; /* Adiciona padding lateral para telas menores */
            background-color: transparent; /* Remove o background do container interno */
            box-shadow: none; /* Remove a sombra do container interno */
            border-top: none; /* Remove a borda do container interno */
        }
        .main-header h1 {
            margin: 0;
            color: #ffffff;
            font-size: 1.8em;
            text-align: left;
        }
        .main-header nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
        }
        .main-header nav ul li {
            margin-left: 25px;
        }
        .main-header nav ul li a {
            color: #ffffff;
            text-decoration: none;
            font-weight: bold;
            transition: color 0.3s ease;
        }
        .main-header nav ul li a:hover {
            color: #007bff;
        }

        /* Ajustes para a seção de herói */
        .hero-section {
            background-color: rgba(0, 0, 0, 0.6); /* Fundo semi-transparente escuro */
            color: #ffffff;
            padding: 80px 0; /* Mais padding para destaque */
            text-align: center;
            flex-grow: 1; /* Faz a seção ocupar o espaço restante */
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
        }
        .hero-section .container {
            background-color: transparent; /* Sem fundo aqui também */
            box-shadow: none;
            border-top: none;
            padding: 0 20px;
        }
        .hero-section h2 {
            font-size: 3em;
            margin-bottom: 20px;
            text-shadow: 2px 2px 5px rgba(0,0,0,0.8); /* Sombra para o texto */
            color: #ffffff; /* Cor branca para o título */
        }
        .hero-section p {
            font-size: 1.3em;
            margin-bottom: 40px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.7);
            color: #e9ecef;
        }
        .hero-section .btn-call-to-action {
            background-color: #007bff;
            color: white;
            padding: 15px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 1.2em;
            font-weight: bold;
            transition: background-color 0.3s ease, transform 0.2s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        .hero-section .btn-call-to-action:hover {
            background-color: #0056b3;
            transform: translateY(-3px);
        }

        /* Seções de conteúdo (Sobre, Serviços, Contato) */
        main {
            flex-shrink: 0; /* Impede que o main encolha */
        }
        .about-section, .services-section, .contact-section {
            background-color: rgba(40, 40, 40, 0.9); /* Fundo escuro semi-transparente para as seções */
            color: #e9ecef;
            padding: 60px 0;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
            margin-bottom: 20px;
        }
        .about-section h3, .services-section h3, .contact-section h3 {
            color: #007bff; /* Títulos em azul */
        }
        .about-section p, .services-section p, .contact-section p {
            color: #ced4da;
        }
        .about-section .container, .services-section .container, .contact-section .container {
            background-color: transparent;
            box-shadow: none;
            border-top: none;
            max-width: 1000px; /* Largura padrão */
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Cards de serviço */
        .service-cards {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
        }
        .service-cards .card {
            background-color: rgba(60, 60, 60, 0.95); /* Fundo dos cards um pouco mais claro */
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.4);
            flex: 1 1 calc(33% - 40px); /* 3 cards por linha */
            max-width: 320px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border-bottom: 4px solid #007bff;
        }
        .service-cards .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.6);
        }
        .service-cards .card h4 {
            color: #007bff;
            font-size: 1.4em;
            margin-bottom: 10px;
        }
        .service-cards .card p {
            color: #ced4da;
            font-size: 0.95em;
        }

        /* Rodapé */
        .main-footer {
            background-color: rgba(26, 26, 26, 0.85); /* Fundo semi-transparente escuro para o rodapé */
            color: #ffffff;
            text-align: center;
            padding: 20px 0;
            margin-top: auto; /* Empurra o rodapé para baixo */
            width: 100%;
        }
        .main-footer .container {
            background-color: transparent;
            box-shadow: none;
            border-top: none;
            padding: 0 20px;
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .main-header .container {
                flex-direction: column;
                text-align: center;
            }
            .main-header nav ul {
                margin-top: 15px;
            }
            .hero-section h2 {
                font-size: 2.2em;
            }
            .hero-section p {
                font-size: 1em;
            }
            .service-cards .card {
                flex: 1 1 100%; /* Cards empilhados em telas pequenas */
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="container">
            <h1>Sua Marca - Soluções Automotivas Completas</h1>
            <nav>
                <ul>
                    <li><a href="#sobre">Sobre a Plataforma</a></li>
                    <li><a href="#servicos">Nossos Serviços</a></li>
                    <li><a href="#contato">Contato</a></li>
                    <li><a href="login.php" class="btn-login">Acessar Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <main>
        <section id="hero" class="hero-section">
            <div class="container">
                <h2>Tudo para o seu veículo em um só lugar.</h2>
                <p>Gerencie sua borracharia, autopeças e mecânica de forma eficiente e integrada.</p>
                <a href="login.php" class="btn-call-to-action">Entrar na Plataforma</a>
            </div>
        </section>

        <section id="sobre" class="about-section">
            <div class="container">
                <h3>Conheça nossa Plataforma</h3>
                <p>Nossa solução digital unifica a gestão de estabelecimentos automotivos, otimizando operações e elevando a satisfação do cliente.</p>
            </div>
        </section>

        <section id="servicos" class="services-section">
            <div class="container">
                <h3>Serviços Integrados</h3>
                <div class="service-cards">
                    <div class="card">
                        <h4>Borracharia</h4>
                        <p>Gestão de estoque, agendamento de serviços, histórico de clientes.</p>
                    </div>
                    <div class="card">
                        <h4>Autopeças</h4>
                        <p>Controle de inventário, vendas, integração direta com mecânica e borracharia.</p>
                    </div>
                    <div class="card">
                        <h4>Mecânica</h4>
                        <p>Ordens de serviço, histórico de veículos, acesso rápido a peças e ferramentas.</p>
                        </div>
                </div>
            </div>
        </section>

        <section id="contato" class="contact-section">
            <div class="container">
                <h3>Fale Conosco</h3>
                <p>Tem dúvidas ou precisa de suporte? Entre em contato!</p>
                <p>Email: contato@suamarca.com</p>
                <p>Telefone: (XX) XXXX-XXXX</p>
            </div>
        </section>
    </main>

    <footer class="main-footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Sua Marca. Todos os direitos reservados.</p>
        </div>
    </footer>
</body>
</html>