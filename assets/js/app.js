import 'bootstrap/js/src/alert';
import Bouncer from 'formbouncerjs';

document.addEventListener('DOMContentLoaded', function() {

    const submitButton = document.querySelector('.newsletter-form button[type=submit]');
    submitButton.disabled = true;
    submitButton.textContent = 'Please fill the form';

    const validateEmail = async (field) => {
        // console.log('custom email validation for field: ', field);

        const hasEmailValidation = field.getAttribute('data-validate-email');
        if (!hasEmailValidation) {
            // console.log('missing data-validate-email attribute');
            return false;
        }

        let validationUrl = field.getAttribute('data-validation-url');
        if (!validationUrl) {
            // console.log('no data-validation-url, field is valid');
            return false;
        }

        // console.log('validating value: ', field.value);

        if (!field.value) {
            // console.log('field has no value');
            return false;
        }

        validationUrl = new URL(validationUrl);
        validationUrl.searchParams.append('value', field.value);

        let response = await fetch(validationUrl);

        response = await response.json();

        return !response.is_valid;
    };

    const validator = new Bouncer('.newsletter-form', {
        emitEvents: true,
        messageAfterField: true,
        customValidations: {
            validateEmail,
        },
        messages: {
            missingValue: {
                default: 'This field is required.',
            },
            validateEmail: 'This email is already subscribed',
            wrongLength: {
                over: 'This field cannot be longer than {max} characters',
            }
        },
    });

    const validateForm = async (event) => {
            const form = event.target.form;

            if (!form || !form.matches('.newsletter-form')) {
                return;
            }

            const categoriesSelected = form.querySelectorAll('input[type="checkbox"]:checked').length;

            const errors = await validator.isValid(form);
            if (errors.length > 0 || !categoriesSelected) {
                submitButton.disabled = true;
                submitButton.textContent = 'Please fill the form';
            } else {
                submitButton.disabled = false;
                submitButton.textContent = 'Subscribe';
            }
    };

    document.addEventListener('blur', validateForm, false);
    document.addEventListener('change', validateForm, false);
}, false);