<?php include 'header.php'; ?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>
    /* --- INTERAÇÃO DO GRID (COMPORTAMENTO LIMPO) --- */
    .sobre-linha-cards:hover .sobre-card-glass {
        opacity: 0.4;
    }

    .sobre-card-glass:hover {
        opacity: 1 !important;
        transform: translateY(-10px) !important;
        border-color: #7da5fb !important;
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.5);
    }
    
    /* --- BARRA DE PROGRESSO --- */
    #scroll-progress {
        position: fixed;
        top: 0;
        left: 0;
        width: 0%;
        height: 4px;
        background: #7da5fb;
        z-index: 9999;
        box-shadow: 0 0 10px rgba(125, 165, 251, 0.5);
    }  
    
    /* --- ANIMAÇÃO DE ENTRADA SUAVE --- */
    @keyframes fadeIn {
        from { 
            opacity: 0; 
            transform: translateY(10px); 
        }
        to { 
            opacity: 1; 
            transform: translateY(0); 
        }
    }

    .sobre-section {
        font-family: Arial, Helvetica, sans-serif !important;
        animation: fadeIn 0.6s ease-out forwards;
        padding: 20px;
    }

    /* Estilo Glassmorphism para os Cartões */
    .sobre-card-glass {
        background: rgba(255, 255, 255, 0.03) !important;
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1) !important;
        border-radius: 20px !important;
        padding: 30px 20px;
        text-align: center;
        flex: 1 1 300px;
        max-width: 340px;
        transition: transform 0.3s ease, opacity 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease !important;
        opacity: 0; 
        animation: fadeIn 0.6s ease-out forwards;
        position: relative;
        word-break: break-all; 
    }

    /* --- ENTRADA EM CASCATA CORRIGIDA --- */
    .sobre-card-glass:nth-child(1) { animation-delay: 0.05s; }
    .sobre-card-glass:nth-child(2) { animation-delay: 0.1s; }
    .sobre-card-glass:nth-child(3) { animation-delay: 0.15s; }

    /* --- EMOJIS SEM DEFORMAÇÃO --- */
    .sobre-icon-glass {
        font-size: 3.5rem; 
        line-height: 1;
        margin-bottom: 20px;
        display: block;
        flex-shrink: 0; 
    }

    /* --- ESTILO DOS NOMES --- */
    .dev-nome {
        color: #7da5fb; 
        font-size: 1.5rem; 
        font-weight: 800; 
        font-family: Verdana, sans-serif; 
        margin-bottom: 10px;
    }

    /* --- LINK DO GITHUB --- */
    .github-link {
        display: inline-block;
        margin-top: 15px;
        color: rgba(255, 255, 255, 0.4);
        font-size: 1.8rem;
        transition: all 0.3s ease;
        text-decoration: none !important;
    }

    .github-link:hover {
        color: #7da5fb;
        transform: scale(1.1);
    }

    /* --- BOTÃO GLOW --- */
    .btn-glow {
        display: inline-flex;
        align-items: center;
        gap: 12px;
        padding: 14px 45px;
        background: #7da5fb !important;
        color: black !important;
        text-decoration: underline !important;
        border-radius: 50px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .btn-glow:hover {
        filter: brightness(1.1);
        transform: scale(1.05);
        box-shadow: 0 0 25px rgba(125, 165, 251, 0.4);
    }

    /* ESTRUTURA DAS LINHAS INDEPENDENTES */
    .sobre-container-geral {
        display: flex;
        flex-direction: column;
        gap: 25px;
        margin-top: 40px;
        width: 100%;
    }

    .sobre-linha-cards {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 25px;
        width: 100%;
    }
</style>

<main class="layout">
    <div class="content">
        <section class="sobre-section">
            <header style="text-align: center; margin-bottom: 50px;">
                <h1 style="font-size: 2.2rem; color: white;">Equipe de Desenvolvimento</h1>
                <p class="muted">Conheça os 5 especialistas por trás do TrackFix.</p>
            </header>

            <div class="sobre-container-geral">
                
                <div class="sobre-linha-cards">
                    
                    <div class="sobre-card-glass">
                        <span class="sobre-icon-glass">👩🏻‍💻</span>
                        <h3 class="dev-nome">Maria Eduarda Sousa</h3>
                        <p class="muted" style="font-size: 0.85rem;">Especialidade 1</p>
                        <hr style="opacity: 0.1; margin: 15px 0;">
                        <p style="line-height: 1.6;">0001140856@senaimgaluno.com.br <br> maduszrodrig@gmail.com</p>
                        <a href="https://github.com/arlebiina" target="_blank" class="github-link">
                            <i class="fab fa-github"></i>
                        </a>
                    </div>

                    <div class="sobre-card-glass">
                        <span class="sobre-icon-glass">💻</span>
                        <h3 class="dev-nome">Maria Laura Leal</h3>
                        <p class="muted" style="font-size: 0.85rem;">Especialidade 2</p>
                        <hr style="opacity: 0.1; margin: 15px 0;">
                        <p style="line-height: 1.6;">0001139340@senaimgaluno.com.br <br> marialaura123fernandes@gmail.com</p>
                        <a href="https://github.com/marialaura123fernandes-lgtm" target="_blank" class="github-link">
                            <i class="fab fa-github"></i>
                        </a>
                    </div>

                    <div class="sobre-card-glass">
                        <span class="sobre-icon-glass">🛡️</span>
                        <h3 class="dev-nome">Izadora Peres</h3>
                        <p class="muted" style="font-size: 0.85rem;">Especialidade 3</p>
                        <hr style="opacity: 0.1; margin: 15px 0;">
                        <p style="line-height: 1.6;">0001138565@senaimgaluno.com.br</p>
                        <a href="https://github.com/izaaaaaaaa" target="_blank" class="github-link">
                            <i class="fab fa-github"></i>
                        </a>
                    </div>

                </div>

                <div class="sobre-linha-cards">
                    
                    <div class="sobre-card-glass">
                        <span class="sobre-icon-glass">🎨</span>
                        <h3 class="dev-nome">Izabella Peres</h3>
                        <p class="muted" style="font-size: 0.85rem;">Especialidade 4</p>
                        <hr style="opacity: 0.1; margin: 15px 0;">
                        <p style="line-height: 1.6;">izabellaperessilvadeoliveira@gmail.com</p>
                        <a href="https://github.com/0001137790-create" target="_blank" class="github-link">
                            <i class="fab fa-github"></i>
                        </a>
                    </div>

                    <div class="sobre-card-glass">
                        <span class="sobre-icon-glass">🔨</span>
                        <h3 class="dev-nome">Luis Felipe Oliveira</h3>
                        <p class="muted" style="font-size: 0.85rem;">Especialidade 5</p>
                        <hr style="opacity: 0.1; margin: 15px 0;">
                        <p style="line-height: 1.6;">0001138704@senaimgaluno.com.br</p>
                        <a href="https://github.com/LuisFelipeOliveiraTeodoro" target="_blank" class="github-link">
                            <i class="fab fa-github"></i>
                        </a>
                    </div>

                </div>

            </div>

            <div style="text-align: center; margin-top: 60px; padding-bottom: 40px;">
                <a href="?rota=home" class="btn-glow">
                    <i class="fas fa-chevron-left"></i> Voltar ao Sistema
                </a>
            </div>
        </section>
    </div>
</main>

<?php include 'footer.php'; ?>
