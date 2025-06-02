document.addEventListener('DOMContentLoaded', function () {
    // Variables globales
    let tasks = JSON.parse(localStorage.getItem('tasks')) || [];
    let currentTaskIndex = null;

    // Elementos del DOM
    const taskList = document.getElementById('task-list');
    const addTaskBtn = document.getElementById('add-task-btn');
    const machineLetter = document.getElementById('machine-letter');
    const machineNumber = document.getElementById('machine-number');
    const taskDescription = document.getElementById('task-description');
    const taskPriority = document.getElementById('task-priority');
    const taskDepartment = document.getElementById('task-department');
    const noteModal = document.getElementById('note-modal');
    const noteTextarea = document.getElementById('note-textarea');
    const saveNoteBtn = document.getElementById('save-note-btn');
    const closeBtn = document.querySelector('.close-btn');

    // Agregar nueva tarea
    addTaskBtn.addEventListener('click', function () {
        const machine = `${machineLetter.value}${machineNumber.value}`;
        const description = taskDescription.value.trim();
        const priority = taskPriority.value;
        const department = taskDepartment.value;

        if (description === '') {
            alert('Ingresa una descripción para la tarea');
            return;
        }

        const newTask = {
            id: Date.now(),
            machine,
            description,
            priority,
            department,
            status: 'Pendiente',
            completed: false,
            notes: '',
            createdAt: new Date().toISOString()
        };

        tasks.push(newTask);
        saveTasks();
        renderTasks();

        // Limpiar campos
        taskDescription.value = '';
    });

    // Renderizar tareas
    function renderTasks() {
        taskList.innerHTML = '';

        if (tasks.length === 0) {
            taskList.innerHTML = '<p style="text-align: center; color: #95a5a6; font-size: 0.9em;">No hay tareas registradas</p>';
            return;
        }

        tasks.slice().reverse().forEach((task, index) => {
            // Determinar la clase de prioridad para la fila completa
            let priorityClass = '';
            if (task.priority === 'Alta') priorityClass = 'high-priority';
            else if (task.priority === 'Pendiente') priorityClass = 'pending-priority';
            else if (task.priority === 'Baja') priorityClass = 'low-priority';

            // Determinar clase de estado
            let statusClass = '';
            if (task.status === 'Proceso') statusClass = 'status-process';
            else if (task.status === 'Detenida') statusClass = 'status-stopped';
            else if (task.status === 'Revicion') statusClass = 'status-review';
            else if (task.status === 'Finalizada') statusClass = 'status-finished';

            const taskItem = document.createElement('div');
            taskItem.className = `task-item ${priorityClass} ${statusClass} ${task.completed ? 'completed' : ''}`;
            taskItem.dataset.id = task.id;

            // Número de tarea
            const taskNumber = document.createElement('div');
            taskNumber.className = 'task-number';
            taskNumber.textContent = (tasks.length - index) + '.';
            taskItem.appendChild(taskNumber);

            // Checkbox
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'task-checkbox';
            checkbox.checked = task.completed;
            checkbox.addEventListener('change', function () {
                task.completed = this.checked;
                task.status = task.completed ? 'Finalizada' : task.status;
                saveTasks();
                renderTasks();
            });

            // Máquina
            const machineSpan = document.createElement('div');
            machineSpan.className = 'task-machine';
            machineSpan.textContent = task.machine;

            // Descripción
            const descriptionSpan = document.createElement('div');
            descriptionSpan.className = 'task-description';
            descriptionSpan.textContent = task.description;

            // Prioridad (texto)
            const prioritySpan = document.createElement('div');
            prioritySpan.className = `task-priority priority-${task.priority.toLowerCase()}`;
            prioritySpan.textContent = task.priority;

            // Estado (dropdown)
            const statusSelect = document.createElement('select');
            statusSelect.className = 'task-status';
            statusSelect.innerHTML = `
                <option value="Pendiente" ${task.status === 'Pendiente' ? 'selected' : ''}>Pendiente</option>
                <option value="Proceso" ${task.status === 'Proceso' ? 'selected' : ''}>Proceso</option>
                <option value="Detenida" ${task.status === 'Detenida' ? 'selected' : ''}>Detenida</option>
                <option value="Revicion" ${task.status === 'Revicion' ? 'selected' : ''}>Revisión</option>
                <option value="Finalizada" ${task.status === 'Finalizada' ? 'selected' : ''}>Finalizada</option>
            `;
            statusSelect.addEventListener('change', function () {
                task.status = this.value;
                if (this.value === 'Finalizada') {
                    task.completed = true;
                }
                saveTasks();
                renderTasks();
            });

            // Departamento (dropdown editable)
            const departmentSelect = document.createElement('select');
            departmentSelect.className = 'task-department';
            departmentSelect.innerHTML = `
                <option value="Eléctrico" ${task.department === 'Eléctrico' ? 'selected' : ''}>Eléctrico</option>
                <option value="Mecánico" ${task.department === 'Mecánico' ? 'selected' : ''}>Mecánico</option>
                <option value="Setup" ${task.department === 'Setup' ? 'selected' : ''}>Setup</option>
            `;
            departmentSelect.addEventListener('change', function () {
                task.department = this.value;
                saveTasks();
            });

            // Botón de nota con contador
            const noteBtn = document.createElement('button');
            noteBtn.className = `action-btn note-btn ${task.notes ? 'has-note' : ''}`;
            const noteCount = task.notes ? task.notes.split('\n---\n').length : 0;
            noteBtn.innerHTML = `<i class="fas fa-comment${noteCount > 0 ? '-dots' : ''} note-indicator"></i> ${noteCount > 0 ? `<span class="note-count">${noteCount}</span>` : ''}`;
            noteBtn.title = noteCount > 0 ? 'Ver notas' : 'Agregar nota';
            noteBtn.addEventListener('click', function () {
                openNoteModal(index);
            });

            // Botón de eliminar
            const deleteBtn = document.createElement('button');
            deleteBtn.className = 'action-btn delete-btn';
            deleteBtn.innerHTML = '<i class="fas fa-trash"></i>';
            deleteBtn.title = 'Eliminar tarea';
            deleteBtn.addEventListener('click', function () {
                if (confirm('¿Eliminar esta tarea?')) {
                    tasks.splice(tasks.length - 1 - index, 1);
                    saveTasks();
                    renderTasks();
                }
            });

            // Acciones
            const actionsDiv = document.createElement('div');
            actionsDiv.className = 'task-actions';
            actionsDiv.appendChild(noteBtn);
            actionsDiv.appendChild(deleteBtn);

            // Construir el elemento de tarea
            taskItem.appendChild(checkbox);
            taskItem.appendChild(machineSpan);
            taskItem.appendChild(descriptionSpan);
            taskItem.appendChild(prioritySpan);
            taskItem.appendChild(statusSelect);
            taskItem.appendChild(departmentSelect);
            taskItem.appendChild(actionsDiv);

            taskList.appendChild(taskItem);
        });
    }

    // Abrir modal de notas
    function openNoteModal(index) {
        currentTaskIndex = index;
        const task = tasks[tasks.length - 1 - index];
        const notes = task.notes ? task.notes.split('\n---\n') : [''];

        noteTextarea.value = notes.join('\n\n---\n\n');
        noteModal.style.display = 'block';
    }

    // Cerrar modal de notas
    closeBtn.addEventListener('click', function () {
        noteModal.style.display = 'none';
    });

    // Guardar nota
    saveNoteBtn.addEventListener('click', function () {
        if (currentTaskIndex !== null) {
            const task = tasks[tasks.length - 1 - currentTaskIndex];
            task.notes = noteTextarea.value.trim();
            saveTasks();
            renderTasks();
            noteModal.style.display = 'none';
        }
    });

    // Función para guardar en la base de datos
    async function saveToDatabase() {
        try {
            const response = await fetch('https://ttscr.com/ls/api/save_tasks.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    tasks: tasks,
                    auth: {
                        user: 'u310879082_lisTa_User',
                        pass: '|6+Ai1s&m?aQ'
                    }
                })
            });

            const result = await response.json();

            if (response.ok) {
                alert('Tareas guardadas exitosamente en la base de datos');
                console.log('Resultado:', result);
            } else {
                throw new Error(result.message || 'Error al guardar en la base de datos');
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error al guardar en la base de datos: ' + error.message);
        }
    }

    // Event listener para el botón de guardar
    document.getElementById('save-db-btn').addEventListener('click', saveToDatabase);

    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', function (event) {
        if (event.target === noteModal) {
            noteModal.style.display = 'none';
        }
    });

    // Guardar tareas en localStorage
    function saveTasks() {
        localStorage.setItem('tasks', JSON.stringify(tasks));
    }

    // Inicializar la aplicación
    renderTasks();
});