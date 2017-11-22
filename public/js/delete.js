// Changes the form's function name to delete and execute then submit the form
document.getElementById('delete').addEventListener('click', function(event) {
    event.preventDefault();
    if(confirm('Are you sure to delete this list?')) {
        document.getElementById('update').name = 'delete';
        document.getElementById('update').click();
    }
});
