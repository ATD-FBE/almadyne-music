'use strict';

import { imgSideSet } from './img_sideset.js';
import { arrangeImages } from './random_img.js';

const formTrigger = document.querySelector('.FormTrigger'),
    mailForm = document.querySelector('#MailFormObject'),
    arrowTopPage = document.querySelector('.ArrowTopPage'),
    submitBtn = document.querySelector('.SubmitButton');

formTrigger.addEventListener('click', () => {
    mailForm.classList.remove('Closed');
    mailForm.classList.add('Open');
    arrowTopPage.classList.remove('Hidden');
    arrowTopPage.classList.add('Visible');

    arrangeImages(imgSideSet);

    const  animationTime = parseFloat(getComputedStyle(mailForm).animationDuration) * 1000;
    setTimeout(() => mailForm.querySelector('input.TextField1').focus(), animationTime);
});

mailForm.addEventListener('submit', () => {
    console.log('+ submit');
    submitBtn.disabled = true;
    submitBtn.innerText = 'Отправка...';
});
