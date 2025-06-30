document.addEventListener('DOMContentLoaded', () => {
    const taskInput = document.getElementById('taskInput');
    const addTaskBtn = document.getElementById('addTaskBtn');
    const taskList = document.getElementById('taskList');

    // Función para renderizar las tareas en la lista
    const renderTask = (task) => {
        const li = document.createElement('li');
        li.dataset.id = task.id;
        if (task.is_completed) {
            li.classList.add('completed');
        }

        const taskText = document.createElement('span');
        taskText.textContent = task.task_name;
        taskText.addEventListener('click', () => toggleComplete(task.id, !task.is_completed));
        
        const deleteBtn = document.createElement('button');
        deleteBtn.textContent = 'Eliminar';
        deleteBtn.addEventListener('click', () => deleteTask(task.id));

        li.appendChild(taskText);
        li.appendChild(deleteBtn);
        taskList.appendChild(li);
    };

    // Cargar las tareas al iniciar la página
    const loadTasks = async () => {
        try {
            const response = await fetch('get_tasks.php');
            const tasks = await response.json();
            taskList.innerHTML = ''; // Limpiar la lista antes de renderizar
            tasks.forEach(renderTask);
        } catch (error) {
            console.error('Error al cargar las tareas:', error);
        }
    };

    // Añadir una nueva tarea
    const addTask = async () => {
        const taskName = taskInput.value.trim();
        if (taskName === '') return;

        try {
            const response = await fetch('save_tasks.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ task_name: taskName }),
            });
            const result = await response.json();
            
            if (result.success) {
                renderTask({ id: result.id, task_name: taskName, is_completed: false });
                taskInput.value = '';
            } else {
                console.error('Error del servidor al guardar la tarea:', result.message);
            }
        } catch (error) {
            console.error('Error al añadir la tarea:', error);
        }
    };

    // Eliminar una tarea
    const deleteTask = async (taskId) => {
        try {
            const response = await fetch('delete_task.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: taskId }),
            });
            const result = await response.json();

            if (result.success) {
                const li = document.querySelector(`li[data-id='${taskId}']`);
                if (li) {
                    li.remove();
                }
            } else {
                console.error('Error del servidor al eliminar la tarea:', result.message);
            }
        } catch (error) {
            console.error('Error al eliminar la tarea:', error);
        }
    };

    // Marcar una tarea como completada o no
    const toggleComplete = async (taskId, isCompleted) => {
         try {
            const response = await fetch('update_task.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: taskId, is_completed: isCompleted }),
            });
            const result = await response.json();

            if (result.success) {
                const li = document.querySelector(`li[data-id='${taskId}']`);
                if (li) {
                    li.classList.toggle('completed', isCompleted);
                    // Actualizar el estado en el DOM para futuros clics
                    const taskText = li.querySelector('span');
                    taskText.onclick = () => toggleComplete(taskId, !isCompleted);
                }
            } else {
                console.error('Error del servidor al actualizar la tarea:', result.message);
            }
        } catch (error) {
            console.error('Error al actualizar la tarea:', error);
        }
    };

    // Event Listeners
    addTaskBtn.addEventListener('click', addTask);
    taskInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            addTask();
        }
    });

    // Carga inicial
    loadTasks();
});