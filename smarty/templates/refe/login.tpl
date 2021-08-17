{extends file='genericpage.tpl'} 
{* Login page from https://startbootstrap.com/snippets/login/ *}
{block name='head'}
    <style>
	body {
	  background: #007bff;
	  background: linear-gradient(to right, #0062E6, #33AEFF);
	}
    </style>
{/block}
{block name="title"}Login{/block}
{block name='body' nocache}
  <div class="container">
    <div class="row">
      <div class="col-sm-9 col-md-7 col-lg-5 mx-auto">
        <div class="card card-signin my-4">
          <div class="card-body">
            <h5 class="card-title text-center">Sign In</h5>
            {if $wronglogin}  <div class="alert alert-warning" role="alert">
                Your email and password are bad. Please try again.
              </div>{/if}

            <form class="p-4 p-md-4 border rounded-3 bg-light mb-3" method="post" action="{$basePath}/login">
              <div class="form-floating mb-3">
                <input type="email" class="form-control" name="inputEmail" id="inputEmail" placeholder="name@example.com">
                <label for="inputEmail">Email address</label>
              </div>
              <div class="form-floating mb-3">
                <input type="password" class="form-control" name="inputPassword" id="inputPassword" placeholder="Password">
                <label for="inputPassword">Password</label>
              </div>
              <div class="checkbox mb-3">
                <label>
                  <input type="checkbox" id="customCheck1" name="rememberMe" value="1"> Remember me
                </label>
              </div>
              <button class="w-100 btn btn-lg btn-primary" type="submit">Login</button>
              <hr class="my-4">
              <small class="text-muted">By clicking Login, you agree to the terms of use.</small>
            </form>
{*
TODO1: add recaptcha middleware to use google/recaptcha library for spam prevention: https://github.com/middlewares/recaptcha
TODO2: add federated login
              <hr class="my-4">
              <button class="btn btn-lg btn-google btn-block text-uppercase" type="submit"><i class="fab fa-google mr-2"></i> Sign in with Google</button>
              <button class="btn btn-lg btn-facebook btn-block text-uppercase" type="submit"><i class="fab fa-facebook-f mr-2"></i> Sign in with Facebook</button>
*}
            <div class="alert alert-info alert-dismissible fade show" role="alert">
              <strong>We need tech cookies to log you in.</strong><br/>
              I hope you are ok with it, so continue your login or read more <a href="" class="alert-link" data-bs-toggle="modal" data-bs-target="#cookiesModalCenteredScrollable">here</a>.
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        
            <!-- Modal -->
            <div class="modal fade" id="cookiesModalCenteredScrollable" tabindex="-1" aria-labelledby="cookiesModalCenteredScrollableTitle" aria-hidden="true">
              <!--<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">-->
              <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                  <div class="modal-header">
                    <h5 class="modal-title" id="cookiesModalCenteredScrollableTitle">Cookies...</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <p><strong>OK, so we need technical cookies to log you in.</strong></p>

                    <p>Every site needs technical cookies to login people. For example, after a valid login we generate a cookie and we save your login tokens into it in a very secure way using software encryption. When you request another page in our site, your browser can send this cookie to us so that we can read it and understand who you are and maintain a valid stateless session.</p>

                    <p>Obviously without this kind of cookie we cannot log you in.</p>
                    
                    <p>Feel free to use the service, in this case we consider that you accept our terms of service and permit the cookie to be saved into your browser.</p>
                    
                    <p>If you don't select the Remember me option the cookie will be deleted when you close the browser. If you select it the cookie lifetime will be higher, so that the next time you'll get into our site we recall of you thanks to the cookie.</p>
                    
                    <p>The cookie will be removed on its expiration date, or when you logout from our site.</p>
                    
                    <p>Thank you!</p>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">I understand</button>
                  </div>
                </div>
              </div>
            </div>
            
          </div>
        </div>
      </div>
    </div>
  </div>
{/block}
