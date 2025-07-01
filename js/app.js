document.addEventListener('DOMContentLoaded', function () {
     // --- ELEMENTOS DEL DOM Y ESTADO ---
     const addTaskBtn = document.getElementById('add-task-btn');
     const taskList = document.getElementById('task-list');
     const credentials = btoa('u310879082_lisTa_User:D14cF]!Ft]');
     let currentTasks = [];
 

     // --- MANEJO DEL MODAL DE NOTAS ---
     const noteModal = document.getElementById('note-modal');
     const closeBtn = document.querySelector('.close-btn');
     const saveNoteBtn = document.getElementById('save-note-btn');
     let currentNoteTaskId = null;
 

     closeBtn.onclick = () => noteModal.style.display = 'none';
     window.onclick = (e) => {
         if (e.target == noteModal) noteModal.style.display = 'none';
     };
 

     saveNoteBtn.onclick = async () => {
         const noteInput = document.getElementById('note-input');
         if (!noteInput.value.trim()) return;
         try {
             await apiCall('notes.php', 'POST', {
                 task_id: currentNoteTaskId,
                 note: noteInput.value.trim()
             });
             noteModal.style.display = 'none';
             loadTasks();
         } catch (error) {
             console.error('Error al guardar nota:', error);
         }
     };
 

     function openNoteModal(task) {
         currentNoteTaskId = task.id;
         document.getElementById('notes-display').innerHTML = task.notes ? task.notes.replace(/\n---\n/g, '<hr>').replace(/\n/g, '<br>') : 'Aún no hay notas.';
         document.getElementById('note-input').value = '';
         noteModal.style.display = 'block';
     }
 

     // --- FUNCIÓN GENÉRICA PARA LLAMADAS A LA API ---
     async function apiCall(endpoint, method = 'GET', body = null) {
         const options = {
             method,
             headers: {
                 'Authorization': `Basic ${credentials}`
             }
         };
         if (body) {
             options.headers['Content-Type'] = 'application/json';
             options.body = JSON.stringify(body);
         }
         const response = await fetch(`api/${endpoint}`, options);
         const responseData = await response.json();
         if (!response.ok) throw new Error(responseData.message || 'Error del servidor');
         return responseData;
     }
 

     // --- RENDERIZADO DE TAREAS ---
     function renderTasks() {
         taskList.innerHTML = '';
         if (currentTasks.length === 0) {
             taskList.innerHTML = '<p style="text-align: center; color: #95a5a6;">No hay tareas pendientes. ¡Agrega una!</p>';
             return;
         }
 

         currentTasks.forEach((task, index) => {
             const statusClass = `status-${task.status.toLowerCase()}`;
             const priorityClass = `priority-${task.priority.toLowerCase()}`;
             const taskItem = document.createElement('div');
             taskItem.className = `task-item ${statusClass} ${priorityClass} ${task.completed ? 'completed' : ''}`;
             taskItem.dataset.id = task.id;
 

             const noteCount = task.notes ? task.notes.split('\n---\n').length : 0;
 

             taskItem.innerHTML = `
                 <div class="task-number">${index + 1}.</div>
                 <input type="checkbox" class="task-checkbox" ${task.completed ? 'checked' : ''}>
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
                     <button class="action-btn note-btn ${task.notes ? 'has-note' : ''}" title="Notas">
                         <i class="fas fa-comment${noteCount > 0 ? '-dots' : ''}"></i>
                         ${noteCount > 0 ? `<span class="note-count">${noteCount}</span>` : ''}
                     </button>
                     <button class="action-btn delete-btn" title="Eliminar"><i class="fas fa-trash"></i></button>
                 </div>
             `;
             taskList.appendChild(taskItem);
         });
     }
 

     // --- LÓGICA DE EVENTOS (DELEGACIÓN) ---
     taskList.addEventListener('change', async (e) => {
         const taskItem = e.target.closest('.task-item');
         if (!taskItem) return;
         const taskId = parseInt(taskItem.dataset.id);
         const task = currentTasks.find(t => t.id === taskId);
         if (!task) return;
 

         const isCheckbox = e.target.classList.contains('task-checkbox');
         const isStatus = e.target.classList.contains('task-status');
         const isDepartment = e.target.classList.contains('task-department-select');
 

         if (isCheckbox) {
             task.completed = e.target.checked;
             task.status = task.completed ? 'Finalizada' : task.status;
         }
         if (isStatus) {
             task.status = e.target.value;
             task.completed = task.status === 'Finalizada';
         }
         if (isDepartment) task.department = e.target.value;
 

         if (isCheckbox || isStatus || isDepartment) {
             try {
                 await apiCall('update_task.php', 'POST', task);
                 loadTasks();
             } catch (error) {
                 console.error('Error al actualizar:', error);
             }
         }
     });
 

     taskList.addEventListener('click', async (e) => {
         const button = e.target.closest('button');
         if (!button) return;
         const taskItem = button.closest('.task-item');
         const taskId = parseInt(taskItem.dataset.id);
 

         if (button.classList.contains('delete-btn')) {
             if (confirm('¿Estás seguro de que quieres eliminar esta tarea?')) {
                 try {
                     await apiCall('delete_task.php', 'POST', {
                         id: taskId
                     });
                     loadTasks();
                 } catch (error) {
                     console.error('Error al eliminar:', error);
                 }
             }
         }
         if (button.classList.contains('note-btn')) {
             const task = currentTasks.find(t => t.id === taskId);
             openNoteModal(task);
         }
     });
 

     addTaskBtn.addEventListener('click', async () => {
         const taskData = {
             machine: document.getElementById('machine-letter').value + document.getElementById('machine-number').value.padStart(2, '0'),
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
         }
     });
 

     // --- CARGA INICIAL ---
     async function loadTasks() {
         try {
             currentTasks = await apiCall('get_tasks.php');
             renderTasks();
         } catch (error) {
             taskList.innerHTML = `<p style="color: red;">Error al cargar: ${error.message}</p>`;
         }
     }
 

     loadTasks();
 });