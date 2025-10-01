// js/signup.js
$(function() {
  $('#signupBtn').click(function(e) {
    e.preventDefault();
    const name = $('#name').val()?.trim();
    const email = $('#email').val()?.trim();
    const password = $('#password').val();

    if (!name || !email || !password) {
      $('#msg').html('<div class="alert alert-danger">All fields are required.</div>');
      return;
    }

    $.ajax({
      url: 'php/signup.php',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ name, email, password }),
      success: function(res) {
        if (res.success) {
          $('#msg').html('<div class="alert alert-success">' + res.message + '</div>');
          // redirect to login after 1s
          setTimeout(() => { window.location.href = 'login.html'; }, 1000);
        } else {
          $('#msg').html('<div class="alert alert-danger">' + (res.message || 'Error') + '</div>');
        }
      },
      error: function(xhr) {
        let msg = 'Server error';
        try { msg = JSON.parse(xhr.responseText).message || msg; } catch(e){}
        $('#msg').html('<div class="alert alert-danger">' + msg + '</div>');
      }
    });
  });
});
