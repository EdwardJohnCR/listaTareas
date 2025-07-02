document.addEventListener('DOMContentLoaded', function () {
    // --- ELEMENTOS DEL DOM Y ESTADO GLOBAL ---
    const addTaskBtn = document.getElementById('add-task-btn');
    const taskList = document.getElementById('task-list');
    const credentials = btoa('u310879082_lisTa_User:D14cF]!Ft]');
    let currentTasks = [];

    // --- MANEJO DEL MODAL DE NOTAS ---
    const noteModal = document.getElementById('note-modal');
    const closeBtn = noteModal.querySelector('.close-btn');
    const saveNoteBtn = document.getElementById('save-note-btn');
    let currentNoteTaskId = null;

    function openNoteModal(task) {
        currentNoteTaskId = task.id;
        document.getElementById('notes-display').innerHTML = task.notes ? task.notes.replace(/\n---\n/g, '<hr>').replace(/\n/g, '<br>') : 'Aún no hay notas.';
        document.getElementById('note-input').value = '';
        noteModal.style.display = 'block';
    }

    closeBtn.onclick = () => noteModal.style.display = 'none';
    window.onclick = (e) => { if (e.target == noteModal) noteModal.style.display = 'none'; };

    saveNoteBtn.onclick = async () => {
        const noteInput = document.getElementById('note-input');
        if (!noteInput.value.trim()) return;
        try {
            await apiCall('notes.php', 'POST', { task_id: currentNoteTaskId, note: noteInput.value.trim() });
            noteModal.style.display = 'none';
            loadTasks();
        } catch (error) { console.error('Error al guardar nota:', error); }
    };

    // --- FUNCIÓN GENÉRICA PARA LLAMADAS A LA API ---
    async function apiCall(endpoint, method = 'GET', body = null) {
        const options = { method, headers: { 'Authorization': `Basic ${credentials}` } };
        if (body) {
            options.headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(body);
        }
        const response = await fetch(`api/${endpoint}`, options);
        if (!response.ok) {
            const errorData = await response.json().catch(() => ({ message: 'El servidor respondió con un error no JSON.' }));
            throw new Error(errorData.message || 'Error desconocido del servidor.');
        }
        return response.json();
    }

    // --- RENDERIZADO DE TAREAS ---
    function renderTasks() {
        taskList.innerHTML = '';
        if (currentTasks.length === 0) {
            taskList.innerHTML = '<p style="text-align: center; color: #95a5a6;">No hay tareas pendientes.</p>';
            return;
        }

        currentTasks.forEach((task) => {
            const isCompleted = task.status === 'Finalizada';
            const statusClass = `status-${task.status.toLowerCase().replace('ó', 'o')}`;
            const priorityClass = `priority-${task.priority.toLowerCase()}`;
            const completedClass = isCompleted ? 'completed' : '';

            const taskItem = document.createElement('div');
            taskItem.className = `task-item ${statusClass} ${priorityClass} ${completedClass}`;
            taskItem.dataset.id = task.id;

            const noteCount = task.notes ? (task.notes.match(/---/g) || []).length + 1 : 0;

            taskItem.innerHTML = `
                <div class="task-number">${task.id}</div>
                <input type="checkbox" class="task-checkbox" ${isCompleted ? 'checked' : ''}>
                <div class="task-machine-wrapper">
                    <div class="task-machine-label">Máquina:</div>
                    <div class="task-machine">${task.machine}</div>
                </div>
                <div class="task-description">${task.description}</div>
                <select class="task-status">
                    <option value="Pendiente" ${task.status === 'Pendiente' ? 'selected' : ''}>Pendiente</option>
                    <option value="Proceso" ${task.status === 'Proceso' ? 'selected' : ''}>Proceso</option>
                    <option value="Revision" ${task.status === 'Revision' ? 'selected' : ''}>Revisión</option>
                    <option value="Detenida" ${task.status === 'Detenida' ? 'selected' : ''}>Detenida</option>
                    <option value="Finalizada" ${task.status === 'Finalizada' ? 'selected' : ''}>Finalizada</option>
                </select>
                <select class="task-department-select">
                    <option value="Eléctrico" ${task.department === 'Eléctrico' ? 'selected' : ''}>Eléctrico</option>
                    <option value="Mecánico" ${task.department === 'Mecánico' ? 'selected' : ''}>Mecánico</option>
                    <option value="Setup" ${task.department === 'Setup' ? 'selected' : ''}>Setup</option>
                </select>
                <div class="task-actions">
                    <button class="action-btn note-btn ${noteCount > 0 ? 'has-note' : ''}" title="Notas"><i class="fas fa-comment"></i></button>
                    <button class="action-btn delete-btn" title="Eliminar"><i class="fas fa-trash"></i></button>
                </div>
            `;
            taskList.appendChild(taskItem);
        });
    }

    // --- CARGA INICIAL DE TAREAS ---
    async function loadTasks() {
        try {
            currentTasks = await apiCall('get_tasks.php');
            renderTasks();
        } catch (error) {
            taskList.innerHTML = `<p style="color: red;">Error al cargar las tareas: ${error.message}</p>`;
        }
    }

    // --- LÓGICA DE EVENTOS ---

    // **AÑADIR NUEVA TAREA**
    async function handleAddTask() {
        const taskData = {
            machine: document.getElementById('machine-letter').value + document.getElementById('machine-number').value,
            description: document.getElementById('task-description').value.trim(),
            priority: document.getElementById('task-priority').value,
            department: document.getElementById('task-department').value,
        };
        if (!taskData.description) {
            alert('La descripción es obligatoria.');
            return;
        }
        try {
            await apiCall('save_tasks.php', 'POST', taskData);
            document.getElementById('task-description').value = '';
            loadTasks();
        } catch (error) {
            console.error('Error al guardar:', error);
            alert(`No se pudo guardar la tarea: ${error.message}`);
        }
    }

    // **INTERACCIONES DENTRO DE LA LISTA**
    async function handleTaskListInteraction(e) {
        const target = e.target;
        const taskItem = target.closest('.task-item');
        if (!taskItem) return;

        const taskId = parseInt(taskItem.dataset.id);
        let task = currentTasks.find(t => t.id === taskId);
        if (!task) return;
        
        task = { ...task }; // Clonar para evitar modificar el estado original directamente

        let needsApiUpdate = false;

        // Click en Botones de Acción
        if (target.closest('button.action-btn')) {
            const button = target.closest('button.action-btn');
            if (button.classList.contains('delete-btn')) {
                if (confirm('¿Estás seguro de que quieres eliminar esta tarea?')) {
                    try {
                        await apiCall('delete_task.php', 'POST', { id: taskId });
                        loadTasks();
                    } catch (error) { console.error('Error al eliminar:', error); }
                }
            } else if (button.classList.contains('note-btn')) {
                openNoteModal(task);
            }
            return;
        }
        
        // Cambio en Checkbox o Selects
        if (target.classList.contains('task-checkbox')) {
            task.completed = target.checked;
            task.status = task.completed ? 'Finalizada' : 'Pendiente';
            needsApiUpdate = true;
        } else if (target.classList.contains('task-status')) {
            task.status = target.value;
            task.completed = task.status === 'Finalizada';
            needsApiUpdate = true;
        } else if (target.classList.contains('task-department-select')) {
            task.department = target.value;
            needsApiUpdate = true;
        }

        if (needsApiUpdate) {
            try {
                await apiCall('update_task.php', 'POST', task);
                loadTasks();
            } catch (error) { 
                console.error('Error al actualizar:', error);
                alert('No se pudo actualizar la tarea. La lista se recargará.');
                loadTasks();
            }
        }
    }

    // --- INICIALIZACIÓN ---
    // Asigna los listeners y carga los datos por primera vez.
    addTaskBtn.addEventListener('click', handleAddTask);
    taskList.addEventListener('click', handleTaskListInteraction);
    taskList.addEventListener('change', handleTaskListInteraction);
    
    loadTasks();
});