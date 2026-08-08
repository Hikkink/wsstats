<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weiss Schwarz Estadísticas</title>
    <!-- Fuente Ubuntu oficial -->
    <link href="https://fonts.googleapis.com/css2?family=Ubuntu:wght@400;700;italic&display=swap" rel="stylesheet">
    <!-- Iconos de FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <!-- BARRA SUPERIOR (Estilo Ubuntu) -->
    <header id="top-bar">
        <div class="top-left">Weiss Schwarz OS</div>
        <!-- Aquí saldrá Fecha y Hora -->
        <div class="top-center" id="clock">Cargando fecha...</div> 
        <div class="top-right">
            <i class="fas fa-wifi"></i>
            <i class="fas fa-volume-up"></i>
            <i class="fas fa-power-off" onclick="updateDB()" title="Actualizar base de datos"></i>
        </div>
    </header>

    <div id="workspace">
        <!-- DOCK IZQUIERDO (Aplicaciones de Estadísticas) -->
        <nav id="ubuntu-dock">
            <!-- Botón de Inicio / Logo de la Página -->
            <div class="dock-item start-btn" onclick="toggleMenu()" title="Panel de Control">
                <img src="boton.png" alt="Logo Weiss Schwarz">
            </div>
            
            <div class="dock-separator"></div>

            <!-- Aplicaciones de Estadísticas -->
            <div class="dock-item" onclick="openPlayerWindow()" title="Perfil de Jugador">
                <i class="fas fa-user" style="color: #00ff88;"></i>
            </div>
            <div class="dock-item" onclick="openDeckStatsWindow()" title="Perfil de Deck">
                <i class="fas fa-inbox" style="color: #00ff88;"></i>
            </div>
            <div class="dock-item" onclick="openBalanceWindow()" title="Balance y Meta">
                <i class="fas fa-balance-scale" style="color: #00ff88;"></i>
            </div>

            <!-- MENÚ DE INICIO (Panel de Control y Registros) -->
            <div id="start-menu" class="hidden">
                <h3>Panel de Control</h3>
                <ul>
                    <li onclick="openMatchWindow()"><i class="fas fa-gamepad" style="color: #ff0055;"></i> Nuevo Enfrentamiento</li>
                    <li onclick="openWindow('window-add-series')"><i class="fas fa-film"></i> Añadir Serie</li>
                    <li onclick="openEditSerieSelector()"><i class="fas fa-pen"></i> Editar Serie</li>
                    <li onclick="openWindow('window-add-player')"><i class="fas fa-user-plus"></i> Añadir Jugador</li>
                    <li onclick="openDeckWindow()"><i class="fas fa-layer-group"></i> Crear Deck</li>
                    <li onclick="openEditDeckSelector()"><i class="fas fa-pen"></i> Editar Deck</li>
                    <li onclick="openRandomDeckWindow()"><i class="fas fa-dice"></i> Elegir Deck Random</li>
                    <li style="border-top: 1px solid #7b2cbf; margin-top: 5px; padding-top: 10px;" onclick="openWindow('window-settings')"><i class="fas fa-database"></i> Configuración BD</li>
                </ul>
            </div>

            <!-- ESCRITORIO (Contenedor de Ventanas) -->
            <main id="desktop">
                
              <!-- 1. VENTANA: CONFIGURACIÓN BD -->
<div id="window-settings" class="window hidden">
    <div class="window-header">
        <span><i class="fas fa-cog"></i> Configuración MySQL</span>
        <button class="close-btn" onclick="closeWindow('window-settings')"></button>
    </div>
    <div class="window-content">
        <form id="form-config" onsubmit="submitForm(event, 'api/save_config.php', 'form-config')">
            <label>IP / Host:</label>
            <input type="text" name="host" value="127.0.0.1" required class="neon-input">
            
            <label>Puerto:</label>
            <input type="number" name="port" value="3306" required class="neon-input" min="1" max="65535">
            
            <label>Nombre BD:</label>
            <input type="text" name="dbname" value="weiss_stats" required class="neon-input">
            
            <label>Usuario:</label>
            <input type="text" name="user" value="root" required class="neon-input">
            
            <label>Contraseña:</label>
            <input type="password" name="pass" class="neon-input">
            
            <button type="submit" class="neon-btn"><i class="fas fa-save"></i> Guardar Configuración</button>
        </form>
        <div id="msg-form-config" class="mensaje"></div>
    </div>
</div>
                <!-- 2. VENTANA: AÑADIR SERIE -->
                <div id="window-add-series" class="window hidden">
                    <div class="window-header">
                        <span><i class="fas fa-film"></i> Añadir Nueva Serie</span>
                        <button class="close-btn" onclick="closeWindow('window-add-series')"></button>
                    </div>
                    <div class="window-content">
                        <!-- CORREGIDO: name="imagen" en lugar de imagen_serie -->
                        <form id="form-series" enctype="multipart/form-data" onsubmit="submitForm(event, 'api/add_series.php', 'form-series')">
                            <label>Nombre de la Serie:</label>
                            <input type="text" name="nombre" required class="neon-input">
                            
                            <label>Cartelera (Imagen):</label>
                            <input type="file" name="imagen" accept="image/*" required class="neon-input">
                            
                            <button type="submit" class="neon-btn"><i class="fas fa-upload"></i> Guardar Serie</button>
                        </form>
                        <div id="msg-form-series" class="mensaje"></div>
                    </div>
                </div>

                <!-- 3. VENTANA: AÑADIR JUGADOR -->
                <div id="window-add-player" class="window hidden">
                    <div class="window-header">
                        <span><i class="fas fa-user-plus"></i> Registrar Jugador</span>
                        <button class="close-btn" onclick="closeWindow('window-add-player')"></button>
                    </div>
                    <div class="window-content">
                        <form id="form-player" onsubmit="submitForm(event, 'api/add_player.php', 'form-player')">
                            <label>Nombre / Alias:</label>
                            <input type="text" name="nombre" required class="neon-input" placeholder="Ej: Cazaputas">
                            
                            <button type="submit" class="neon-btn"><i class="fas fa-user-check"></i> Guardar Jugador</button>
                        </form>
                        <div id="msg-form-player" class="mensaje"></div>
                    </div>
                </div>

                <!-- 4. VENTANA: AÑADIR DECK -->
                <div id="window-add-deck" class="window hidden">
                    <div class="window-header">
                        <span><i class="fas fa-layer-group"></i> Crear Nuevo Deck</span>
                        <button class="close-btn" onclick="closeWindow('window-add-deck')"></button>
                    </div>
                    <div class="window-content">
                        <form id="form-deck" onsubmit="submitForm(event, 'api/add_deck.php', 'form-deck')">
                            <label>Serie del Deck:</label>
                            <select name="serie_id" id="select-series" required class="neon-input">
                                <option value="">Cargando series...</option>
                            </select>

                            <label>Nombre del Deck:</label>
                            <input type="text" name="nombre" required class="neon-input" placeholder="Ej: SAO Sinon Sniper">
                            
                            <label>Colores (Selecciona 1 a 4):</label>
                            <div class="colores-grid">
                                <label><input type="checkbox" name="colores[]" value="RED"> 🔴 Red</label>
                                <label><input type="checkbox" name="colores[]" value="BLUE"> 🔵 Blue</label>
                                <label><input type="checkbox" name="colores[]" value="GREEN"> 🟢 Green</label>
                                <label><input type="checkbox" name="colores[]" value="YELLOW"> 🟡 Yellow</label>
                            </div>

                            <label>Decklist (lista de cartas):</label>
                            <textarea name="decklist" class="neon-input decklist-input" rows="6" placeholder="Ej:&#10;3x Sinon Sniper&#10;4x Asuna, Flash"></textarea>

                            <button type="submit" class="neon-btn"><i class="fas fa-plus"></i> Guardar Deck</button>
                        </form>
                        <div id="msg-form-deck" class="mensaje"></div>
                    </div>
                </div>

                <!-- 5. VENTANA: ENFRENTAMIENTO -->
                <div id="window-add-match" class="window hidden" style="width: 650px;">
                    <div class="window-header">
                        <span><i class="fas fa-gamepad"></i> Mesa de Enfrentamiento</span>
                        <button class="close-btn" onclick="closeWindow('window-add-match')"></button>
                    </div>
                    <div class="window-content">
                        <form id="form-match" onsubmit="submitForm(event, 'api/add_match.php', 'form-match')">
                            
                            <div class="match-layout">
                                <!-- JUGADOR 1 -->
                                <div class="player-col">
                                    <h4>PLAYER 1</h4>
                                    <select name="p1_id" id="p1-select" required class="neon-input"></select>
                                    <select id="p1-serie-select" onchange="loadDecks(this.value, 'p1-deck-select')" class="neon-input">
                                        <option value="">Selecciona Serie</option>
                                    </select>
                                    <select name="p1_deck_id" id="p1-deck-select" required class="neon-input">
                                        <option value="">Seleccione Serie primero</option>
                                    </select>
                                </div>

                                <!-- VS -->
                                <div class="vs-col">VS</div>

                                <!-- JUGADOR 2 -->
                                <div class="player-col">
                                    <h4>PLAYER 2</h4>
                                    <select name="p2_id" id="p2-select" required class="neon-input"></select>
                                    <select id="p2-serie-select" onchange="loadDecks(this.value, 'p2-deck-select')" class="neon-input">
                                        <option value="">Selecciona Serie</option>
                                    </select>
                                    <select name="p2_deck_id" id="p2-deck-select" required class="neon-input">
                                        <option value="">Seleccione Serie primero</option>
                                    </select>
                                </div>
                            </div>

                            <!-- RESULTADO -->
                            <div class="winner-section">
                                <label><i class="fas fa-trophy"></i> ¿Quién ganó?</label>
                                <select name="winner" required class="neon-input winner-select">
                                    <option value="1">Ganó Player 1</option>
                                    <option value="2">Ganó Player 2</option>
                                </select>
                            </div>

                            <button type="submit" class="neon-btn match-btn"><i class="fas fa-khanda"></i> Registrar Resultado</button>
                        </form>
                        <div id="msg-form-match" class="mensaje"></div>
                    </div>
                </div>

                <!-- 6. PERFIL JUGADOR -->
                <div id="window-profile" class="window hidden" style="width: 700px;">
                    <div class="window-header">
                        <span><i class="fas fa-user"></i> Perfil Jugador</span>
                        <button class="close-btn" onclick="closeWindow('window-profile')"></button>
                    </div>
                    <div class="window-content">
                        <select id="profile-player-select" onchange="loadPlayerStats(this.value)" class="neon-input">
                            <option value="">Selecciona un jugador...</option>
                        </select>
                        <div id="player-stats-container" class="dashboard-grid hidden">
                            <!-- El JS inyectará los datos aquí -->
                        </div>
                    </div>
                </div>

                <!-- 7. PERFIL DECK -->
                <div id="window-deck-stats" class="window hidden" style="width: 650px;">
                    <div class="window-header">
                        <span><i class="fas fa-inbox"></i> Rendimiento de Deck</span>
                        <button class="close-btn" onclick="closeWindow('window-deck-stats')"></button>
                    </div>
                    <div class="window-content">
                        <select id="profile-deck-select" onchange="loadDeckStats(this.value)" class="neon-input">
                            <option value="">Selecciona un deck...</option>
                        </select>
                        <div id="deck-stats-container" class="dashboard-grid hidden">
                            <!-- El JS inyectará los datos aquí -->
                        </div>
                    </div>
                </div>

                <!-- 8. BALANCE / META -->
                <div id="window-balance" class="window hidden" style="width: 750px;">
                    <div class="window-header">
                        <span><i class="fas fa-balance-scale"></i> Balance y Meta Global</span>
                        <button class="close-btn" onclick="closeWindow('window-balance')"></button>
                    </div>
                    <div class="window-content" id="balance-stats-container">
                        <!-- El JS inyectará los datos aquí -->
                    </div>
                </div>
<!-- EDITAR SERIE -->
<div id="window-edit-serie" class="window hidden" style="width: 500px;">
    <div class="window-header">
        <span><i class="fas fa-pen"></i> Editar Serie</span>
        <button class="close-btn" onclick="closeWindow('window-edit-serie')">✕</button>
    </div>
    <div class="window-content">
        <label>Selecciona una serie:</label>
        <select id="edit-serie-select" class="neon-input" onchange="loadSerieForEdit(this.value)">
            <option value="">Cargando series...</option>
        </select>
        <hr style="margin: 15px 0; border-color: #7b2cbf;">
        <form id="form-edit-serie" enctype="multipart/form-data" onsubmit="submitEditSerie(event)">
            <input type="hidden" name="id" id="edit-serie-id">
            <label>Nombre:</label>
            <input type="text" name="nombre" id="edit-serie-nombre" required class="neon-input">
            <label>Nueva Imagen (dejar vacío para mantener):</label>
            <input type="file" name="imagen" accept="image/*" class="neon-input">
            <div style="margin-top:10px;">
                <img id="edit-serie-preview" src="" alt="Vista previa" style="max-width:100px; max-height:100px; border-radius:8px; border:2px solid #7b2cbf;">
            </div>
            <button type="submit" class="neon-btn"><i class="fas fa-save"></i> Actualizar Serie</button>
        </form>
        <div id="msg-form-edit-serie" class="mensaje"></div>
    </div>
</div>

<!-- EDITAR DECK -->
<div id="window-edit-deck" class="window hidden" style="width: 500px;">
    <div class="window-header">
        <span><i class="fas fa-pen"></i> Editar Deck</span>
        <button class="close-btn" onclick="closeWindow('window-edit-deck')">✕</button>
    </div>
    <div class="window-content">
        <label>Selecciona un deck:</label>
        <select id="edit-deck-select" class="neon-input" onchange="loadDeckForEdit(this.value)">
            <option value="">Cargando decks...</option>
        </select>
        <hr style="margin: 15px 0; border-color: #7b2cbf;">
        <form id="form-edit-deck" onsubmit="submitEditDeck(event)">
            <input type="hidden" name="id" id="edit-deck-id">
            <label>Nombre:</label>
            <input type="text" name="nombre" id="edit-deck-nombre" required class="neon-input">
            <label>Colores:</label>
            <div class="colores-grid" id="edit-deck-colores-container"></div>
            <label>Decklist (lista de cartas):</label>
            <textarea name="decklist" id="edit-deck-decklist" class="neon-input decklist-input" rows="6"></textarea>
            <button type="submit" class="neon-btn"><i class="fas fa-save"></i> Actualizar Deck</button>
        </form>
        <button type="button" class="neon-btn neon-btn-danger" id="btn-delete-deck" style="display:none; margin-top:10px;" onclick="deleteDeck()"><i class="fas fa-trash"></i> Eliminar Deck</button>
        <div id="msg-form-edit-deck" class="mensaje"></div>
    </div>
</div>

<!-- ELEGIR DECK RANDOM -->
<div id="window-random-deck" class="window hidden" style="width: 420px;">
    <div class="window-header">
        <span><i class="fas fa-dice"></i> Elegir Deck Random</span>
        <button class="close-btn" onclick="closeWindow('window-random-deck')"></button>
    </div>
    <div class="window-content">
        <div id="random-deck-result">
            <p class="text-neon" style="font-size:16px;"><i class="fas fa-dice"></i> Toca el dado para elegir un deck</p>
        </div>
        <button class="neon-btn" style="margin-top:15px;" onclick="pickRandomDeck()"><i class="fas fa-dice-d6"></i> Deck Aleatorio</button>
    </div>
</div>

<!-- ACTUALIZAR BASE DE DATOS -->
<div id="window-db-update" class="window hidden" style="width: 400px;">
    <div class="window-header">
        <span><i class="fas fa-database"></i> Base de Datos</span>
        <button class="close-btn" onclick="closeWindow('window-db-update')"></button>
    </div>
    <div class="window-content">
        <div id="db-update-result">
            <p class="text-neon" style="font-size:16px;"><i class="fas fa-spinner fa-spin"></i> Verificando...</p>
        </div>
    </div>
</div>

    <script src="js/script.js"></script>
</body>
</html>