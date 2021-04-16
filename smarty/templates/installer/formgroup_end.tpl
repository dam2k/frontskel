                <div class="valid-feedback">{$_valid_feedback_arr}</div>
                <div class="invalid-feedback"><ul>{foreach $_invalid_feedback_arr as $err}<li>{$err}</li>{/foreach}</ul></div>
              </div>
              <small id="{$_inputname}Help" class="form-text text-muted">
                {$_helptext}
              </small>
            </div>
