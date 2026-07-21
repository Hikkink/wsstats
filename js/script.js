// 1. RELOJ Y FECHA DE LA BARRA SUPERIOR
function updateClock() {
    const now = new Date();
    
    // Obtener la hora
    const timeString = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    
    // Obtener la fecha en formato corto (Ej: 15 oct. 2023)
    const dateOptions = { day: '2-digit', month: 'short', year: 'numeric' };
    const dateString = now.toLocaleDateString('es-ES', dateOptions);
    
    // Unir ambos en la barra superior
    document.getElementById('clock').textContent = dateString + '  |  ' + timeString;
}
setInterval(updateClock, 1000);
updateClock();

// 2. MANEJO DE MENÚ Y VENTANAS
let highestZIndex = 10;

function toggleMenu() {
    document.getElementById('start-menu').classList.toggle('hidden');
}

function openWindow(windowId) {
    document.getElementById('start-menu').classList.add('hidden'); // Ocultar menú
    const win = document.getElementById(windowId);
    if(win) {
        win.classList.remove('hidden');
        
        // Traer ventana al frente
        highestZIndex++;
        win.style.zIndex = highestZIndex;
        
        // Posicionar en el centro de forma escalonada si es la primera vez que se abre
        if (!win.style.top || win.style.top === '') {
            win.style.top = (50 + (highestZIndex * 5)) + 'px';
            win.style.left = (150 + (highestZIndex * 5)) + 'px';
        }
    }
}

function closeWindow(windowId) {
    const win = document.getElementById(windowId);
    if(win) {
        win.classList.add('hidden');
    }
}

// 3. EVENTO PARA CARGAR SERIES EN LA VENTANA DE "AÑADIR DECK"
function openDeckWindow() {
    openWindow('window-add-deck');
    let select = document.getElementById('select-series');
    select.innerHTML = '<option value="">Cargando...</option>';

    fetch('api/get_series.php')
    .then(response => response.json())
    .then(data => {
        select.innerHTML = '<option value="">Selecciona una serie</option>';
        data.forEach(serie => {
            select.innerHTML += `<option value="${serie.id}">${serie.nombre}</option>`;
        });
    })
    .catch(error => {
        select.innerHTML = '<option value="">Error al cargar series</option>';
        console.error('Error loading series:', error);
    });
}

// 4. EVENTO PARA LA MESA DE ENFRENTAMIENTOS (Cargar Jugadores y Series)
function openMatchWindow() {
    openWindow('window-add-match');
    
    // Cargar Jugadores en los dos selects (P1 y P2)
    fetch('api/get_players.php')
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">Selecciona Jugador</option>';
            data.forEach(p => options += `<option value="${p.id}">${p.nombre}</option>`);
            document.getElementById('p1-select').innerHTML = options;
            document.getElementById('p2-select').innerHTML = options;
        })
        .catch(error => console.error('Error loading players:', error));

    // Cargar Series en los dos selects (P1 y P2)
    fetch('api/get_series.php')
        .then(response => response.json())
        .then(data => {
            let options = '<option value="">Selecciona Serie</option>';
            data.forEach(s => options += `<option value="${s.id}">${s.nombre}</option>`);
            document.getElementById('p1-serie-select').innerHTML = options;
            document.getElementById('p2-serie-select').innerHTML = options;
        })
        .catch(error => console.error('Error loading series:', error));
}
// --- FUNCIONES PARA EDITAR SERIE ---
function openEditSerie(serieId) {
    // Obtener datos de la serie por ID (puedes usar get_series.php o crear un endpoint específico)
    fetch(`api/get_series.php?id=${serieId}`)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }
            // Asumimos que get_series.php devuelve un array con un solo objeto si se pasa id
            const serie = data[0] || data;
            document.getElementById('edit-serie-id').value = serie.id;
            document.getElementById('edit-serie-nombre').value = serie.nombre;
            document.getElementById('edit-serie-preview').src = serie.imagen_url || '';
            openWindow('window-edit-serie');
        })
        .catch(error => {
            console.error('Error al cargar serie:', error);
            alert('Error al cargar la serie');
        });
}

function submitEditSerie(event) {
    event.preventDefault();
    const form = document.getElementById('form-edit-serie');
    const formData = new FormData(form);
    const msgBox = document.getElementById('msg-form-edit-serie');
    
    msgBox.innerHTML = "<i class='fas fa-spinner fa-spin'></i> Actualizando...";
    msgBox.style.color = "#c77dff";

    fetch('api/edit_serie.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            msgBox.innerHTML = `<i class='fas fa-check'></i> ${data.success}`;
            msgBox.style.color = "#00ff88";
            // Cerrar ventana y recargar datos si es necesario
            setTimeout(() => {
                closeWindow('window-edit-serie');
                // Aquí puedes refrescar la lista de series si la tienes visible
            }, 1500);
        } else {
            msgBox.innerHTML = `<i class='fas fa-times'></i> ${data.error}`;
            msgBox.style.color = "#ff0055";
        }
    })
    .catch(error => {
        msgBox.innerHTML = "<i class='fas fa-times'></i> Error en la conexión.";
        msgBox.style.color = "#ff0055";
        console.error('Error:', error);
    });
}

// --- FUNCIONES PARA EDITAR DECK ---
function openEditDeck(deckId) {
    // Obtener datos del deck por ID (necesitarías un endpoint para obtener un deck específico o ya lo tienes en get_decks_by_series)
    // Mejor crear un api/get_deck.php?id=...
    fetch(`api/get_deck.php?id=${deckId}`)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }
            document.getElementById('edit-deck-id').value = data.id;
            document.getElementById('edit-deck-nombre').value = data.nombre;
            
            // Marcar checkboxes de colores
            const colores = data.colores ? data.colores.split(',') : [];
            const container = document.getElementById('edit-deck-colores-container');
            container.innerHTML = ''; // Limpiar
            const colorOptions = ['RED','BLUE','GREEN','YELLOW'];
            const colorLabels = {'RED':'🔴 Red','BLUE':'🔵 Blue','GREEN':'🟢 Green','YELLOW':'🟡 Yellow'};
            colorOptions.forEach(c => {
                const label = document.createElement('label');
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'colores[]';
                checkbox.value = c;
                if (colores.includes(c)) checkbox.checked = true;
                label.appendChild(checkbox);
                label.appendChild(document.createTextNode(' ' + colorLabels[c]));
                container.appendChild(label);
                container.appendChild(document.createElement('br'));
            });
            
            openWindow('window-edit-deck');
        })
        .catch(error => {
            console.error('Error al cargar deck:', error);
            alert('Error al cargar el deck');
        });
}

function submitEditDeck(event) {
    event.preventDefault();
    const form = document.getElementById('form-edit-deck');
    const formData = new FormData(form);
    const msgBox = document.getElementById('msg-form-edit-deck');
    
    msgBox.innerHTML = "<i class='fas fa-spinner fa-spin'></i> Actualizando...";
    msgBox.style.color = "#c77dff";

    fetch('api/edit_deck.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            msgBox.innerHTML = `<i class='fas fa-check'></i> ${data.success}`;
            msgBox.style.color = "#00ff88";
            setTimeout(() => {
                closeWindow('window-edit-deck');
            }, 1500);
        } else {
            msgBox.innerHTML = `<i class='fas fa-times'></i> ${data.error}`;
            msgBox.style.color = "#ff0055";
        }
    })
    .catch(error => {
        msgBox.innerHTML = "<i class='fas fa-times'></i> Error en la conexión.";
        msgBox.style.color = "#ff0055";
        console.error('Error:', error);
    });
}

// 5. FILTRAR DECKS DINÁMICAMENTE SEGÚN LA SERIE SELECCIONADA EN EL ENFRENTAMIENTO
function loadDecks(serieId, targetSelectId) {
    let select = document.getElementById(targetSelectId);
    
    if (!serieId) {
        select.innerHTML = '<option value="">Seleccione Serie primero</option>';
        return;
    }

    select.innerHTML = '<option value="">Buscando decks...</option>';
    
    fetch(`api/get_decks_by_series.php?serie_id=${serieId}`)
        .then(response => response.json())
        .then(data => {
            if (!data || data.length === 0) {
                select.innerHTML = '<option value="">Sin decks registrados</option>';
                return;
            }
            
            let options = '<option value="">Selecciona Deck</option>';
            data.forEach(d => {
                // Mostrar colores junto al nombre visualmente
                let coloresStr = d.colores ? ` [${d.colores}]` : '';
                options += `<option value="${d.id}">${d.nombre}${coloresStr}</option>`;
            });
            select.innerHTML = options;
        })
        .catch(() => {
            select.innerHTML = '<option value="">Error al cargar decks</option>';
        });
}

// 6. ENVÍO DE FORMULARIOS POR AJAX (No recarga la página)
function submitForm(event, url, formId) {
    event.preventDefault();
    
    let form = document.getElementById(formId);
    let formData = new FormData(form);
    let msgBox = document.getElementById('msg-' + formId);
    
    msgBox.innerHTML = "<i class='fas fa-spinner fa-spin'></i> Procesando...";
    msgBox.style.color = "#c77dff";

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        msgBox.innerHTML = `<i class='fas fa-check'></i> ${data}`;
        msgBox.style.color = "#00ff88"; // Verde Neon
        
        // Limpiar el formulario si el registro fue exitoso (excepto configuración)
        if(formId !== 'form-config') {
            form.reset();
            
            // Si estábamos en el match, resetear los select de decks
            if(formId === 'form-match') {
                document.getElementById('p1-deck-select').innerHTML = '<option value="">Seleccione Serie primero</option>';
                document.getElementById('p2-deck-select').innerHTML = '<option value="">Seleccione Serie primero</option>';
            }
        }
    })
    .catch(error => {
        msgBox.innerHTML = "<i class='fas fa-times'></i> Error en la conexión.";
        msgBox.style.color = "#ff0055"; // Rojo Neon
        console.error('Form submission error:', error);
    });
}

// 7. SISTEMA DE DRAG & DROP (Arrastrar las ventanas)
document.querySelectorAll('.window').forEach(win => {
    const header = win.querySelector('.window-header');
    let isDragging = false, startX, startY, initialX, initialY;

    // Al hacer clic en cualquier parte de la ventana, traerla al frente
    win.addEventListener('mousedown', () => {
        highestZIndex++;
        win.style.zIndex = highestZIndex;
    });

    // Iniciar arrastre solo desde la barra superior de la ventana
    if(header) {
        header.addEventListener('mousedown', (e) => {
            isDragging = true;
            startX = e.clientX;
            startY = e.clientY;
            initialX = win.offsetLeft;
            initialY = win.offsetTop;
            document.body.style.userSelect = 'none'; // Evitar seleccionar texto sin querer
        });
    }

    // Mover ventana
    document.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        const dx = e.clientX - startX;
        const dy = e.clientY - startY;
        win.style.left = (initialX + dx) + 'px';
        win.style.top = (initialY + dy) + 'px';
    });

    // Soltar ventana
    document.addEventListener('mouseup', () => {
        isDragging = false;
        document.body.style.userSelect = 'auto';
    });
});

// --- 8. FUNCIONES PARA ESTADÍSTICAS ---

// A. PERFIL DE JUGADOR
function openPlayerWindow() {
    openWindow('window-profile');
    fetch('api/get_players.php')
        .then(r => r.json())
        .then(data => {
            let options = '<option value="">Selecciona un jugador...</option>';
            data.forEach(p => options += `<option value="${p.id}">${p.nombre}</option>`);
            document.getElementById('profile-player-select').innerHTML = options;
            document.getElementById('player-stats-container').classList.add('hidden');
        })
        .catch(error => console.error('Error loading players for profile:', error));
}

// A. PERFIL JUGADOR (Actualizado con imágenes)
function loadPlayerStats(playerId) {
    if(!playerId) {
        document.getElementById('player-stats-container').classList.add('hidden');
        return;
    }
    
    const container = document.getElementById('player-stats-container');
    container.innerHTML = '<p class="text-neon">Cargando...</p>';
    container.classList.remove('hidden');

    fetch(`api/stats_player.php?id=${playerId}`)
        .then(r => r.json())
        .then(data => {
            let total = parseInt(data.stats.total);
            let wins = parseInt(data.stats.wins);
            let winrate = total > 0 ? ((wins / total) * 100).toFixed(1) : 0;

            let mejorDeck = {nombre: "N/A", img: ""}, 
                peorDeck = {nombre: "N/A", img: ""}, 
                masUsado = {nombre: "N/A", img: ""};
            let maxWins = -1, maxUses = -1, maxLoss = -1;

            if(data.decks && data.decks.length > 0) {
                data.decks.forEach(d => {
                    let j = parseInt(d.jugados), w = parseInt(d.wins), l = j - w;
                    if(j > maxUses) { maxUses = j; masUsado = {nombre: d.nombre, img: d.imagen_url}; }
                    if(w > maxWins) { maxWins = w; mejorDeck = {nombre: d.nombre, img: d.imagen_url}; }
                    if(l > maxLoss) { maxLoss = l; peorDeck = {nombre: d.nombre, img: d.imagen_url}; }
                });
            }

            // Helper para mostrar la miniatura si existe
            const getThumb = (img) => img ? `<img src="${img}" class="deck-thumb" alt="Deck image">` : '';

            let html = `
                <div class="stat-card">
                    <h3>Resumen General</h3>
                    <span class="text-neon">${winrate}% WINRATE</span>
                    <p style="text-align:center; margin-top:10px;">${wins} W / ${total - wins} L</p>
                </div>
                <div class="stat-card">
                    <h3>Rendimiento de Decks Propios</h3>
                    <p style="margin-bottom:8px;">Más usado: <br>${getThumb(masUsado.img)} <span class="text-neon" style="font-size:14px;">${masUsado.nombre}</span></p>
                    <p style="margin-bottom:8px;">Mejor Deck: <br>${getThumb(mejorDeck.img)} <span class="text-win">${mejorDeck.nombre}</span></p>
                    <p>Peor Deck: <br>${getThumb(peorDeck.img)} <span class="text-loss">${peorDeck.nombre}</span></p>
                </div>
                <div class="stat-card" style="grid-column: span 2;">
                    <h3>Némesis (Decks contra los que más pierde)</h3>
                    ${data.nemesis && data.nemesis.length > 0 ? 
                        data.nemesis.map(n => `<p style="margin-bottom:5px;">- ${getThumb(n.imagen_url)} Pierde contra <b class="text-loss">${n.nombre}</b> (${n.derrotas} veces)</p>`).join('') : 
                        '<p>Aún no hay derrotas registradas.</p>'
                    }
                </div>
            `;
            container.innerHTML = html;
        })
        .catch(error => {
            container.innerHTML = '<p class="text-error">Error al cargar estadísticas del jugador</p>';
            console.error('Error loading player stats:', error);
        });
}

// B. PERFIL DE DECK
// B. PERFIL DE DECK - Corregido
function openDeckStatsWindow() {
    openWindow('window-deck-stats');
    const select = document.getElementById('profile-deck-select');
    select.innerHTML = '<option value="">Cargando decks...</option>';
    
    // Primero cargar todas las series
    fetch('api/get_series.php')
        .then(response => response.json())
        .then(series => {
            if (series.error) {
                select.innerHTML = `<option value="">Error: ${series.error}</option>`;
                return;
            }
            
            if (!Array.isArray(series) || series.length === 0) {
                select.innerHTML = '<option value="">No hay series disponibles</option>';
                return;
            }
            
            // Limpiar y agregar opción por defecto
            select.innerHTML = '<option value="">Selecciona un deck...</option>';
            
            // Contador para saber cuándo terminar
            let seriesCargadas = 0;
            let totalDecks = 0;
            
            // Para cada serie, cargar sus decks
            series.forEach(serie => {
                fetch(`api/get_decks_by_series.php?serie_id=${serie.id}`)
                    .then(response => response.json())
                    .then(decks => {
                        if (Array.isArray(decks) && decks.length > 0) {
                            decks.forEach(deck => {
                                const coloresStr = deck.colores ? ` [${deck.colores}]` : '';
                                select.innerHTML += `<option value="${deck.id}">${serie.nombre} - ${deck.nombre}${coloresStr}</option>`;
                                totalDecks++;
                            });
                        }
                        seriesCargadas++;
                        
                        // Si ya cargamos todas las series y no hay decks
                        if (seriesCargadas === series.length && totalDecks === 0) {
                            select.innerHTML = '<option value="">No hay decks disponibles en ninguna serie</option>';
                        }
                    })
                    .catch(error => {
                        console.error(`Error cargando decks de serie ${serie.id}:`, error);
                        seriesCargadas++;
                    });
            });
        })
        .catch(error => {
            select.innerHTML = '<option value="">Error al cargar series</option>';
            console.error('Error:', error);
        });
}

// B. PERFIL DECK (Actualizado con imágenes)
function loadDeckStats(deckId) {
    if (!deckId) {
        document.getElementById('deck-stats-container').classList.add('hidden');
        return;
    }
    
    const container = document.getElementById('deck-stats-container');
    container.classList.remove('hidden');
    container.innerHTML = '<p class="text-neon">Cargando estadísticas del deck...</p>';

    fetch(`api/stats_deck.php?id=${deckId}`)
        .then(r => r.json())
        .then(data => {
            // Manejar errores
            if (data.error) {
                container.innerHTML = `<p class="text-error">${data.error}</p>`;
                return;
            }

            // Función para mostrar miniatura (si existe)
            const getThumb = (img) => {
                if (img) {
                    return `<img src="${img}" class="deck-thumb" alt="Imagen del deck">`;
                }
                return `<div class="deck-thumb default"><i class="fas fa-image"></i></div>`;
            };

            // Si no hay partidas
            if (data.mensaje && data.stats.total === 0) {
                container.innerHTML = `
                    <div class="stat-card" style="grid-column: span 2; text-align: center;">
                        <div style="display: flex; align-items: center; justify-content: center; gap: 15px; margin-bottom: 15px;">
                            ${getThumb(data.info.imagen_url)}
                            <div>
                                <h3>${data.info.deck_nombre}</h3>
                                <p style="font-size: 14px; color: #aaa;">${data.info.serie_nombre}</p>
                                ${data.info.colores ? `<p style="font-size: 12px; color: #888;">[${data.info.colores}]</p>` : ''}
                            </div>
                        </div>
                        <p class="text-neon" style="margin-top: 20px; font-size: 18px;">
                            <i class="fas fa-info-circle"></i> ${data.mensaje}
                        </p>
                        <p style="color: #888; margin-top: 10px;">Juega partidas con este deck para ver estadísticas detalladas</p>
                    </div>
                `;
                return;
            }

            // Generar HTML para Matchups Fuerte
            let fuerteHtml = '';
            if (data.fuerte && data.fuerte.length > 0) {
                fuerteHtml = data.fuerte.map(f => `
                    <p style="margin-bottom: 8px;" class="text-win">
                        ${getThumb(f.imagen_url)} 
                        <span style="font-weight: bold;">${f.nombre}</span> 
                        <span style="font-size: 12px; color: #00ff88;">(${f.wins} Wins)</span>
                    </p>
                `).join('');
            } else {
                fuerteHtml = '<p style="color: #888;">No hay suficientes datos</p>';
            }

            // Generar HTML para Matchups Débil
            let debilHtml = '';
            if (data.debil && data.debil.length > 0) {
                debilHtml = data.debil.map(d => `
                    <p style="margin-bottom: 8px;" class="text-loss">
                        ${getThumb(d.imagen_url)} 
                        <span style="font-weight: bold;">${d.nombre}</span> 
                        <span style="font-size: 12px; color: #ff0055;">(${d.losses} Losses)</span>
                    </p>
                `).join('');
            } else {
                debilHtml = '<p style="color: #888;">No hay suficientes datos</p>';
            }

            // Generar HTML para Jugadores
            let playersHtml = '';
            if (data.players && data.players.length > 0) {
                playersHtml = data.players.map(p => `
                    <p style="margin-bottom: 5px;">
                        <span style="font-weight: bold;">${p.jugador_nombre}</span> 
                        <span style="font-size: 12px; color: #888;">(${p.partidas_jugadas} partidas, ${p.winrate}% WR)</span>
                    </p>
                `).join('');
            } else {
                playersHtml = '<p style="color: #888;">No hay jugadores registrados</p>';
            }

            // Generar HTML para Evolución
            let evolutionHtml = '';
            if (data.evolution && data.evolution.length > 0) {
                evolutionHtml = data.evolution.map(e => `
                    <span class="badge ${e.resultado === 'Victoria' ? 'badge-win' : 'badge-loss'}">
                        ${e.resultado}
                    </span>
                `).join(' ');
            } else {
                evolutionHtml = '<p style="color: #888;">No hay historial</p>';
            }

            // HTML completo
            container.innerHTML = `
                <!-- Tarjeta Principal del Deck -->
                <div class="stat-card" style="grid-column: span 2;">
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 15px;">
                        ${getThumb(data.info.imagen_url)}
                        <div>
                            <h3 style="margin: 0;">${data.info.deck_nombre}</h3>
                            <p style="font-size: 14px; color: #aaa; margin: 5px 0;">${data.info.serie_nombre}</p>
                            ${data.info.colores ? `<p style="font-size: 12px; color: #888;">[${data.info.colores}]</p>` : ''}
                        </div>
                    </div>
                    <div style="text-align: center;">
                        <span class="text-neon" style="font-size: 28px;">${data.stats.winrate}%</span>
                        <p style="margin: 5px 0;">
                            ${data.stats.wins} Victorias / ${data.stats.losses} Derrotas 
                            <span style="color: #888; font-size: 12px;">(${data.stats.total} partidas)</span>
                        </p>
                    </div>
                </div>

                <!-- Matchups Fuerte -->
                <div class="stat-card">
                    <h3>🟢 Fuerte contra</h3>
                    ${fuerteHtml}
                </div>

                <!-- Matchups Débil -->
                <div class="stat-card">
                    <h3>🔴 Débil contra</h3>
                    ${debilHtml}
                </div>

                <!-- Jugadores que lo usaron -->
                <div class="stat-card">
                    <h3>👤 Jugadores</h3>
                    ${playersHtml}
                </div>

                <!-- Evolución reciente -->
                <div class="stat-card">
                    <h3>📊 Evolución reciente</h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 5px; justify-content: center;">
                        ${evolutionHtml}
                    </div>
                </div>
            `;

            // Agregar estilos para los badges
            const style = document.createElement('style');
            style.textContent = `
                .badge-win {
                    background: #00ff8833;
                    color: #00ff88;
                    padding: 4px 10px;
                    border-radius: 12px;
                    font-size: 12px;
                    border: 1px solid #00ff8866;
                }
                .badge-loss {
                    background: #ff005533;
                    color: #ff0055;
                    padding: 4px 10px;
                    border-radius: 12px;
                    font-size: 12px;
                    border: 1px solid #ff005566;
                }
                .deck-thumb {
                    width: 40px;
                    height: 40px;
                    border-radius: 8px;
                    object-fit: cover;
                    border: 2px solid #7b2cbf;
                }
                .deck-thumb.default {
                    background: #1a1a2e;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    color: #7b2cbf;
                    font-size: 20px;
                }
                .text-win { color: #00ff88; }
                .text-loss { color: #ff0055; }
                .text-neon { color: #c77dff; font-weight: bold; }
                .stat-card {
                    background: #1a1a2e;
                    padding: 15px;
                    border-radius: 12px;
                    border: 1px solid #2a2a4a;
                }
            `;
            container.appendChild(style);

        })
        .catch(error => {
            container.innerHTML = '<p class="text-error">Error al cargar estadísticas del deck</p>';
            console.error('Error loading deck stats:', error);
        });
}
function loadPlayerStats(playerId) {
    if(!playerId) return;
    const container = document.getElementById('player-stats-container');
    container.innerHTML = '<p class="text-neon"><i class="fas fa-spinner fa-spin"></i> Cargando estadísticas...</p>';
    container.classList.remove('hidden');

    fetch(`api/stats_player.php?id=${playerId}`).then(r => r.json()).then(data => {
        let total = parseInt(data.stats.total || 0);
        let wins = parseInt(data.stats.wins || 0);
        let winrate = total > 0 ? ((wins / total) * 100).toFixed(1) : 0;

        let maxWins = -1, maxUses = -1, maxLoss = -1;
        let masUsado = null, mejorDeck = null, peorDeck = null;

        // Analizar y calcular porcentajes de los decks
        if(data.decks) {
            data.decks.forEach(d => {
                let j = parseInt(d.jugados);
                let w = parseInt(d.wins);
                let l = j - w;
                
                // Calcular uso y winrate individual
                d.uso_pct = total > 0 ? ((j / total) * 100).toFixed(0) : 0;
                d.winrate = j > 0 ? ((w / j) * 100).toFixed(0) : 0;
                d.derrotas = l;

                if(j > maxUses) { maxUses = j; masUsado = d; }
                if(w > maxWins) { maxWins = w; mejorDeck = d; }
                if(l > maxLoss) { maxLoss = l; peorDeck = d; }
            });
        }

        // Función auxiliar para renderizar la miniatura
        const getThumbHTML = (img) => {
            if(img) return `<img src="${img}" class="profile-deck-img">`;
            return `<div class="profile-deck-img default"><i class="fas fa-image"></i></div>`;
        };

        // Generar Tarjetas de Decks (Validando que existan)
        let htmlDecks = '';
        if(masUsado) {
            htmlDecks += `
            <div class="profile-deck-card">
                ${getThumbHTML(masUsado.imagen_url)}
                <div class="profile-deck-info">
                    <span class="label">Más Usado (${masUsado.uso_pct}% Pick rate)</span>
                    <span class="name">${masUsado.nombre}</span>
                    <span class="stats">${masUsado.jugados} Jugados | WR: ${masUsado.winrate}%</span>
                </div>
            </div>`;
        }
        if(mejorDeck && mejorDeck.wins > 0) {
            htmlDecks += `
            <div class="profile-deck-card best">
                ${getThumbHTML(mejorDeck.imagen_url)}
                <div class="profile-deck-info">
                    <span class="label" style="color:#00ff88;">Mejor Rendimiento</span>
                    <span class="name text-win">${mejorDeck.nombre}</span>
                    <span class="stats">${mejorDeck.wins} Victorias</span>
                </div>
            </div>`;
        }
        if(peorDeck && peorDeck.derrotas > 0) {
            htmlDecks += `
            <div class="profile-deck-card worst">
                ${getThumbHTML(peorDeck.imagen_url)}
                <div class="profile-deck-info">
                    <span class="label" style="color:#ff0055;">Peor Rendimiento</span>
                    <span class="name text-loss">${peorDeck.nombre}</span>
                    <span class="stats">${peorDeck.derrotas} Derrotas</span>
                </div>
            </div>`;
        }
        if(!htmlDecks) htmlDecks = '<p>Aún no ha jugado con ningún deck.</p>';

        // Generar Tarjetas Némesis
        let htmlNemesis = '';
        if(data.nemesis && data.nemesis.length > 0) {
            htmlNemesis = data.nemesis.map(n => `
                <div class="nemesis-card">
                    ${getThumbHTML(n.imagen_url)}
                    <div>
                        <span class="nemesis-name">${n.nombre}</span>
                        <span class="nemesis-losses"><i class="fas fa-skull"></i> Te ha derrotado ${n.derrotas} veces</span>
                    </div>
                </div>
            `).join('');
        } else {
            htmlNemesis = '<p style="color:#00ff88;"><i class="fas fa-shield-alt"></i> ¡Este jugador aún no tiene derrotas registradas!</p>';
        }

        // Ensamblar todo el HTML del dashboard
        container.innerHTML = `
            <div class="stat-card">
                <h3>Resumen General</h3>
                <div class="wr-container">
                    <span class="wr-percentage">${winrate}% WR</span>
                    <div class="wr-bar-bg">
                        <div class="wr-bar-fill" style="width: ${winrate}%;"></div>
                    </div>
                    <span class="wr-stats-text">${wins} Victorias - ${total - wins} Derrotas (${total} Partidas)</span>
                </div>
            </div>
            
            <div class="stat-card">
                <h3>Rendimiento de Decks Propios</h3>
                ${htmlDecks}
            </div>
            
            <div class="stat-card" style="grid-column: span 2;">
                <h3>Némesis (Mayores Derrotas)</h3>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    ${htmlNemesis}
                </div>
            </div>
        `;
    });
}

// C. BALANCE Y META GLOBAL
function openBalanceWindow() {
    openWindow('window-balance');
    const container = document.getElementById('balance-stats-container');
    container.innerHTML = '<p class="text-neon">Analizando el Meta...</p>';

    fetch('api/stats_balance.php')
        .then(r => r.json())
        .then(data => {
            let decksHtml = '';
            if(data.decks && data.decks.length > 0) {
                decksHtml = data.decks.map((d, index) => {
                    let wr = d.total_jugados > 0 ? ((d.victorias / d.total_jugados) * 100).toFixed(1) : 0;
                    let thumb = d.imagen_url ? `<img src="${d.imagen_url}" class="deck-thumb" alt="Deck image">` : '';
                    return `<div class="deck-rank deck-rank-flex">
                        <div><b>#${index + 1}</b> ${thumb} <b>${d.deck_nombre}</b> <i>(${d.serie})</i> ${d.colores ? `[${d.colores}]` : ''}</div> 
                        <span class="text-win">WR: ${wr}% (${d.total_jugados} J)</span>
                    </div>`;
                }).join('');
            } else {
                decksHtml = '<p>No hay partidas registradas suficientes.</p>';
            }

            let seriesHtml = '';
            if(data.series && data.series.length > 0) {
                seriesHtml = data.series.map(s => {
                    let img = s.imagen_url ? `<img src="${s.imagen_url}" alt="Serie image">` : '<div style="height:120px; background:#1a1a2e;"></div>';
                    return `<div class="series-card">
                        ${img}
                        <p><b>${s.nombre}</b></p>
                        <p class="text-neon" style="font-size:14px;">${s.victorias} Wins</p>
                    </div>`;
                }).join('');
            } else {
                seriesHtml = '<p>No hay series ganadoras aún.</p>';
            }

            container.innerHTML = `
                <div class="stat-card">
                    <h3>Tier List (Top Decks por Winrate)</h3>
                    ${decksHtml}
                </div>
                <div class="stat-card" style="margin-top:15px;">
                    <h3>Las Series Más Dominantes</h3>
                    <div class="series-meta-container">
                        ${seriesHtml}
                    </div>
                </div>
            `;
        })
        .catch(error => {
            container.innerHTML = '<p class="text-error">Error al cargar estadísticas del meta</p>';
            console.error('Error loading balance stats:', error);
        });
}

// 9. FUNCIÓN PARA MANEJAR LA SELECCIÓN DE SERIE EN EL FORMULARIO DE DECK
// Esta función se llama desde el HTML cuando se selecciona una serie
function handleSerieSelection(serieId, formId) {
    // Puedes agregar lógica adicional aquí si es necesario
    console.log(`Serie seleccionada: ${serieId} en el formulario ${formId}`);
}

// --- Editar Serie ---
function openEditSerieSelector() {
    // Cargar series en el select
    const select = document.getElementById('edit-serie-select');
    select.innerHTML = '<option value="">Cargando series...</option>';
    
    // Abrir la ventana primero
    openWindow('window-edit-serie');
    
    fetch('api/get_series.php')
        .then(r => r.json())
        .then(data => {
            if (data.error) throw new Error(data.error);
            select.innerHTML = '<option value="">Selecciona una serie</option>';
            data.forEach(serie => {
                select.innerHTML += `<option value="${serie.id}">${serie.nombre}</option>`;
            });
            // Limpiar formulario y preview
            document.getElementById('edit-serie-id').value = '';
            document.getElementById('edit-serie-nombre').value = '';
            document.getElementById('edit-serie-preview').src = '';
            document.getElementById('msg-form-edit-serie').innerHTML = '';
        })
        .catch(error => {
            select.innerHTML = `<option value="">Error: ${error.message}</option>`;
            console.error('Error cargando series:', error);
        });
}

function loadSerieForEdit(serieId) {
    if (!serieId) {
        document.getElementById('edit-serie-id').value = '';
        document.getElementById('edit-serie-nombre').value = '';
        document.getElementById('edit-serie-preview').src = '';
        return;
    }
    // AHORA USA get_series.php con ID (ya que lo modificaste)
    fetch(`api/get_series.php?id=${serieId}`)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }
            document.getElementById('edit-serie-id').value = data.id;
            document.getElementById('edit-serie-nombre').value = data.nombre;
            document.getElementById('edit-serie-preview').src = data.imagen_url || '';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar la serie');
        });
}
// --- EDITAR DECK ---
function openEditDeckSelector() {
    const select = document.getElementById('edit-deck-select');
    select.innerHTML = '<option value="">Cargando decks...</option>';
    openWindow('window-edit-deck');
    // Ahora usamos get_decks_by_series.php sin parámetros
    fetch('api/get_decks_by_series.php')
        .then(r => r.json())
        .then(data => {
            if (data.error) throw new Error(data.error);
            select.innerHTML = '<option value="">Selecciona un deck</option>';
            data.forEach(deck => {
                let label = deck.nombre;
                if (deck.serie_nombre) label += ` (${deck.serie_nombre})`;
                select.innerHTML += `<option value="${deck.id}">${label}</option>`;
            });
            document.getElementById('edit-deck-id').value = '';
            document.getElementById('edit-deck-nombre').value = '';
            document.getElementById('edit-deck-colores-container').innerHTML = '';
            document.getElementById('msg-form-edit-deck').innerHTML = '';
        })
        .catch(error => {
            select.innerHTML = `<option value="">Error: ${error.message}</option>`;
            console.error('Error cargando decks:', error);
        });
}

function loadDeckForEdit(deckId) {
    if (!deckId) {
        document.getElementById('edit-deck-id').value = '';
        document.getElementById('edit-deck-nombre').value = '';
        document.getElementById('edit-deck-colores-container').innerHTML = '';
        return;
    }
    // Usamos el mismo endpoint con parámetro id
    fetch(`api/get_decks_by_series.php?id=${deckId}`)
        .then(r => r.json())
        .then(data => {
            if (data.error) {
                alert('Error: ' + data.error);
                return;
            }
            document.getElementById('edit-deck-id').value = data.id;
            document.getElementById('edit-deck-nombre').value = data.nombre;

            const colores = data.colores ? data.colores.split(',') : [];
            const container = document.getElementById('edit-deck-colores-container');
            container.innerHTML = '';
            const colorOptions = ['RED','BLUE','GREEN','YELLOW'];
            const colorLabels = {
                'RED':'🔴 Red',
                'BLUE':'🔵 Blue',
                'GREEN':'🟢 Green',
                'YELLOW':'🟡 Yellow'
            };
            colorOptions.forEach(c => {
                const label = document.createElement('label');
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.name = 'colores[]';
                checkbox.value = c;
                if (colores.includes(c)) checkbox.checked = true;
                label.appendChild(checkbox);
                label.appendChild(document.createTextNode(' ' + colorLabels[c]));
                container.appendChild(label);
                container.appendChild(document.createElement('br'));
            });
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error al cargar el deck');
        });
}

