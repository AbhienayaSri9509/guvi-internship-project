// js/login.js
$(function() {
  $('#loginBtn').click(function(e) {
    e.preventDefault();
    const email = $('#email').val()?.trim();
    const password = $('#password').val();

    if (!email || !password) {
      $('#msg').html('<div class="alert alert-danger">Email and password required.</div>');
      return;
    }

    $.ajax({
      url: 'php/login.php',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify({ email, password }),
      success: function(res) {
        if (res.success && res.token) {
          // store token in localStorage (client side session)
          localStorage.setItem('sessionToken', res.token);
          localStorage.setItem('userName', res.user?.name ?? '');
          // redirect to profile page
          window.location.href = 'profile.html';
        } else {
          $('#msg').html('<div class="alert alert-danger">' + (res.message || 'Login failed') + '</div>');
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
