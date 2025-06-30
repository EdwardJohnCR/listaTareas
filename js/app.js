document.addEventListener('DOMContentLoaded', () => {
    const taskInput = document.getElementById('taskInput');
    const addTaskBtn = document.getElementById('addTaskBtn');
    const taskList = document.getElementById('taskList');

    // --- FUNCIÓN PARA RENDERIZAR UNA TAREA EN LA PÁGINA ---
    const renderTask = (task) => {
        const li = document.createElement('li');
        li.dataset.id = task.id; // Guardar el ID de la base de datos
        if (task.is_completed) {
            li.classList.add('completed');
        }

        const taskText = document.createElement('span');
        taskText.textContent = task.task_name;
        // Evento para marcar como completada (¡mejora!)
        taskText.addEventListener('click', () => toggleComplete(task.id, !task.is_completed));
        
        const deleteBtn = document.createElement('button');
        deleteBtn.textContent = 'Eliminar';
        deleteBtn.className = 'delete-btn'; // Clase para estilos
        // Evento para eliminar
        deleteBtn.addEventListener('click', () => deleteTask(task.id));

        li.appendChild(taskText);
        li.appendChild(deleteBtn);
        taskList.appendChild(li);
    };

    // --- CARGAR TAREAS DESDE LA BASE DE DATOS ---
    const loadTasks = async () => {
        try {
            // Apunta al script correcto en la carpeta /api/
            const response = await fetch('api/get_tasks.php');
            if (!response.ok) throw new Error('Error en la respuesta del servidor.');
            
            const tasks = await response.json();
            taskList.innerHTML = ''; // Limpiar la lista actual
            tasks.forEach(renderTask); // Renderizar cada tarea obtenida
        } catch (error) {
            console.error('Error al cargar las tareas:', error);
            taskList.innerHTML = '<li>Error al cargar las tareas. Revisa la consola.</li>';
        }
    };

    // --- AÑADIR UNA NUEVA TAREA ---
    const addTask = async () => {
        const taskName = taskInput.value.trim();
        if (taskName === '') return;

        try {
            // Apunta al script correcto en la carpeta /api/
            const response = await fetch('api/task.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ task_name: taskName }),
            });

            if (!response.ok) throw new Error('Error en la respuesta del servidor.');

            const result = await response.json();
            
            if (result.success) {
                // Si el backend confirma que guardó, la añadimos al frontend
                renderTask({ id: result.id, task_name: taskName, is_completed: false });
                taskInput.value = '';
                taskInput.focus();
            } else {
                console.error('Error del servidor al guardar la tarea:', result.message);
            }
        } catch (error) {
            console.error('Error al añadir la tarea:', error);
        }
    };

    // --- ELIMINAR UNA TAREA (¡Función nueva y necesaria!) ---
    const deleteTask = async (taskId) => {
        if (!confirm('¿Estás seguro de que quieres eliminar esta tarea?')) return;

        try {
            // Necesitas crear este archivo: api/delete_task.php
            const response = await fetch('api/delete_task.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: taskId }),
            });
            
            if (!response.ok) throw new Error('Error en la respuesta del servidor.');

            const result = await response.json();
            if (result.success) {
                const li = document.querySelector(`li[data-id='${taskId}']`);
                if (li) li.remove();
            } else {
                console.error('Error del servidor al eliminar:', result.message);
            }
        } catch (error) {
            console.error('Error al eliminar la tarea:', error);
        }
    };
    
    // --- MARCAR COMO COMPLETADA (¡Función nueva y necesaria!) ---
    const toggleComplete = async (taskId, isCompleted) => {
        try {
            // Necesitas crear este archivo: api/update_task.php
            const response = await fetch('api/update_task.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: taskId, is_completed: isCompleted }),
            });
            
            if (!response.ok) throw new Error('Error en la respuesta del servidor.');
            
            const result = await response.json();
            if (result.success) {
                const li = document.querySelector(`li[data-id='${taskId}']`);
                if (li) li.classList.toggle('completed', isCompleted);
            } else {
                 console.error('Error del servidor al actualizar:', result.message);
            }
        } catch (error) {
            console.error('Error al actualizar la tarea:', error);
        }
    };


    // --- EVENT LISTENERS ---
    addTaskBtn.addEventListener('click', addTask);
    taskInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            addTask();
        }
    });

    // --- CARGA INICIAL ---
    loadTasks();
});