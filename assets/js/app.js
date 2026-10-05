window.isiForm = function (row = {}) {
  const form = document.getElementById('formModul');
  const fields = document.getElementById('isiModal');
  const title = document.getElementById('judulModal');

  if (!form || !fields || !title) return;

  let id = fields.querySelector('[name="id"]');
  if (!id) {
    id = document.createElement('input');
    id.type = 'hidden';
    id.name = 'id';
    fields.append(id);
  }
  id.value = row.id ?? '';

  fields.querySelectorAll('.outlet-field').forEach((field) => field.remove());

  const addField = (name, labelText, value, multiline = false) => {
    const wrapper = document.createElement('div');
    wrapper.className = 'mb-3 outlet-field';

    const label = document.createElement('label');
    label.className = 'form-label';
    label.htmlFor = `outlet-${name}`;
    label.textContent = labelText;

    const input = document.createElement(multiline ? 'textarea' : 'input');
    input.className = 'form-control';
    input.id = `outlet-${name}`;
    input.name = name;
    input.value = value ?? '';
    if (name === 'nama') input.required = true;
    if (multiline) input.rows = 3;

    wrapper.append(label, input);
    fields.append(wrapper);
  };

  addField('nama', 'Nama outlet', row.nama);
  addField('alamat', 'Alamat', row.alamat, true);
  addField('tlp', 'Telepon', row.tlp);
  title.textContent = row.id ? 'Ubah Outlet' : 'Tambah Outlet';
};