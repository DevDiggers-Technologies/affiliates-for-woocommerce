"use strict";

import './admin.less';

const ddwcaf = jQuery.noConflict();

document.addEventListener('DOMContentLoaded', () => {
    // Add Row functionality
    document.addEventListener('click', (e) => {
        if (e.target.matches('.ddwcaf-add-row') || e.target.closest('.ddwcaf-add-row')) {
            e.preventDefault();
            const button = e.target.closest('.ddwcaf-add-row');
            const templateId = button.getAttribute('data-template');
            const maxIndexInput = button.closest('.ddfw-fields-section').querySelector('#ddwcaf-max-index');
            let maxIndex = parseInt(maxIndexInput.value, 10);
            
            // Increment the index
            maxIndex++;
            maxIndexInput.value = maxIndex;
            
            // Get the template and compile it
            const template = wp.template(templateId);
            const html = template({ key: maxIndex });
            
            // Insert the new row before the "Add Row" button row
            const tbody = button.closest('tbody');
            const addRowTr = button.closest('tr');
            addRowTr.insertAdjacentHTML('beforebegin', html);
            
            // Re-initialize Select2 for the new row
            const newRow = addRowTr.previousElementSibling;
            initializeSelect2InRow(newRow);
        }
        // Remove Row functionality
        else if (e.target.matches('.ddwcaf-remove-row') || e.target.closest('.ddwcaf-remove-row')) {
            e.preventDefault();
            const removeButton = e.target.closest('.ddwcaf-remove-row');
            const row = removeButton.closest('tr');
            
            if (confirm('Are you sure you want to remove this row?')) {
                row.remove();
            }
        }
    });
    
    // Initialize Select2 for all select elements in a given row
    function initializeSelect2InRow(row) {
        // Use the global framework functions
        if (typeof window.ddfwInitializeProductsSelect2 === 'function') {
            window.ddfwInitializeProductsSelect2(row);
        }
        
        if (typeof window.ddfwInitializeCategoriesSelect2 === 'function') {
            window.ddfwInitializeCategoriesSelect2(row);
        }
        
        if (typeof window.ddfwInitializeSelect2 === 'function') {
            window.ddfwInitializeSelect2(row);
        }
    }

    // Global Copy URL functionality
    document.addEventListener('click', (e) => {
        const copyBtn = e.target.closest('.ddwcaf-copy-url-btn');
        if (!copyBtn) return;

        const targetId = copyBtn.getAttribute('data-target');
        const targetInput = document.getElementById(targetId);
        
        if (targetInput) {
            const textToCopy = targetInput.innerText || targetInput.textContent;
            
            const performCopy = () => {
                const originalHTML = copyBtn.innerHTML;
                copyBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#01CC44" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                
                setTimeout(() => {
                    copyBtn.innerHTML = originalHTML;
                }, 2000);
            };

            if (navigator.clipboard) {
                navigator.clipboard.writeText(textToCopy).then(() => {
                    performCopy();
                }).catch(err => {
                    console.error('Failed to copy: ', err);
                });
            } else {
                const tempTextArea = document.createElement('textarea');
                tempTextArea.value = textToCopy;
                document.body.appendChild(tempTextArea);
                tempTextArea.select();
                try {
                    document.execCommand('copy');
                    performCopy();
                } catch (err) {
                    console.error('Fallback copy failed: ', err);
                }
                document.body.removeChild(tempTextArea);
            }
        }
    });
});
