// Espera a que todo el HTML esté cargado
document.addEventListener('DOMContentLoaded', function () {

    // --- ELEMENTOS DEL DOM ---
    const addTaskBtn = document.getElementById('add-task-btn');
    const taskList = document.getElementById('task-list');

    // --- ¡LÍNEA CLAVE! ---
    // Aquí se definen el usuario y la contraseña que se enviarán al servidor.
    // Deben coincidir EXACTAMENTE con los de tu config.php
    const credentials = btoa('u310879082_lisTa_User:D14cF]!Ft]');

    // --- FUNCIÓN PARA AGREGAR TAREAS ---
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
            await saveTaskToDB(taskData);
            loadTasks(); // Vuelve a cargar la lista después de guardar
        } catch (error) {
            console.error("FALLO al guardar:", error);
            alert(`Error guardando la tarea: ${error.message}`);
        }
    }

    // --- FUNCIÓN PARA GUARDAR EN LA BD ---
    async function saveTaskToDB(task) {
        const response = await fetch('api/save_tasks.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Basic ${credentials}` // Usa las credenciales
            },
            body: JSON.stringify(task)
        });
        const responseData = await response.json();
        if (!response.ok) throw new Error(responseData.message || 'Error del servidor.');
        return responseData;
    }

    // --- FUNCIÓN PARA CARGAR LAS TAREAS ---
    async function loadTasks() {
        try {
            const response = await fetch('api/get_tasks.php', {
                headers: {
                    'Authorization': `Basic ${credentials}` // Usa las credenciales
                }
            });

            if (!response.ok) {
                 const errorData = await response.json();
                 throw new Error(errorData.message || `Error del servidor: ${response.status}`);
            }

            const tasks = await response.json();
            
            taskList.innerHTML = ''; 
            
            if (tasks.length === 0) {
                taskList.innerHTML = '<p>No hay tareas pendientes. ¡Agrega una!</p>';
            } else {
                tasks.forEach(task => {
                    const taskItem = document.createElement('div');
                    taskItem.className = 'task-item';
                    taskItem.innerHTML = `
                        <strong>${task.machine}</strong> - 
                        <span>${task.description}</span> 
                        <em>(${task.priority})</em>`;
                    taskList.appendChild(taskItem);
                });
            }

        } catch (error) {
            console.error("Error al cargar las tareas:", error);
            taskList.innerHTML = `<p style="color: red;">No se pudieron cargar las tareas: ${error.message}</p>`;
        }
    }

    // Asignar el evento al botón
    addTaskBtn.addEventListener('click', handleAddTask);

    // Carga inicial de tareas
    loadTasks();
});