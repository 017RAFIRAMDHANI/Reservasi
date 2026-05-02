document.querySelectorAll('[data-confirm]').forEach((element) => {
    element.addEventListener('click', (e) => {
        const message = element.getAttribute('data-confirm') || 'Apakah Anda yakin?';
        if (!confirm(message)) {
            e.preventDefault();
        }
    });
});

const startTimeSelect = document.querySelector('[data-start-time]');
const endTimeSelect = document.querySelector('[data-end-time]');

if (startTimeSelect && endTimeSelect) {
    const endOptionsTemplate = Array.from(endTimeSelect.options).map((option) => ({
        value: option.value,
        text: option.text,
    }));

    const refreshEndTimeOptions = () => {
        const startValue = startTimeSelect.value;
        const currentEndValue = endTimeSelect.value;

        endTimeSelect.innerHTML = '';

        endOptionsTemplate.forEach((optionData) => {
            if (optionData.value === '' || startValue === '' || optionData.value > startValue) {
                const option = document.createElement('option');
                option.value = optionData.value;
                option.textContent = optionData.text;
                endTimeSelect.appendChild(option);
            }
        });

        const hasCurrentValue = Array.from(endTimeSelect.options).some((option) => option.value === currentEndValue);
        if (hasCurrentValue) {
            endTimeSelect.value = currentEndValue;
        } else {
            endTimeSelect.selectedIndex = 0;
        }
    };

    startTimeSelect.addEventListener('change', refreshEndTimeOptions);
    refreshEndTimeOptions();
}
