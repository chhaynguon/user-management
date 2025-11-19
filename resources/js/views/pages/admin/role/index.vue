<script setup>
import { FilterMatchMode } from '@primevue/core/api';
import { onMounted, ref } from 'vue';
import RoleService from '@/service/RoleService';
import { useToast } from 'primevue';

const toast = useToast();
const roles = ref([]);
const role = ref();
const deleteRoleDialog = ref(false);
const deleteRolesDialog = ref(false);
const roleDialog = ref(false);
const dt = ref();
const selectedRoles = ref([]);
const submitted = ref(false);

onMounted(async () => {
    await fetchRoles();
});

const fetchRoles = async () => {
    try {
        const res = await RoleService.findAll();
        console.log(res)
        roles.value = res.data;
        console.log("Roles:", roles.value)
    } catch (err) {
        console.error('Failed to fetch roles:', err);
    }
}

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS }
});

function exportCSV() {
    dt.value.exportCSV();
}

function openNew() {
    role.value = {};
    submitted.value = false;
    roleDialog.value = true;
}
function hideDialog() {
    roleDialog.value = false;
    submitted.value = false;
}

function editRole(selectedRole) {
    role.value = { ...selectedRole };
    roleDialog.value = true;
}


async function saveRole() {
    submitted.value = true;

    if (role.value.code?.trim() && role.value.name?.trim() && role.value.description?.trim()) {
        try {
            if (!role.value.id) {
                // Create new role
                const res = await RoleService.create({
                    code: role.value.code,
                    name: role.value.name,
                    description: role.value.description,
                });

                // Add the newly created role to the list
                roles.value.push(res.data);

                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: 'Role Created',
                    life: 3000
                });
            } else {
                // Update existing role
                const res = await RoleService.update(role.value.id, {
                    code: role.value.code,
                    name: role.value.name,
                    description: role.value.description,
                });

                roles.value[index] = res.data;

                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: 'role Updated',
                    life: 3000
                });
            }

            // Reset form
            roleDialog.value = false;
            role.value = {};
            submitted.value = false;

        } catch (error) {
            console.error(error);
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: error.response?.data?.message || 'Failed to save role',
                life: 3000
            });
        }
    }
}

function deleteRole() {
    try {
        RoleService.delete(role.value.id); // make sure roleService has a delete method
        roles.value = roles.value.filter(u => u.id !== role.value.id);
        refresh();
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Role Deleted', life: 3000 });
        deleteRoleDialog.value = false;
        role.value = {};
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete role', life: 3000 });
    }
}

function deleteSelectedRoles() {
    try {
        const ids = selectedRoles.value.map(u => u.id);
        Promise.all(ids.map(id => RoleService.delete(id))); // call API for each
        roles.value = roles.value.filter(u => !ids.includes(u.id));
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Roles Deleted', life: 3000 });
        deleteRolesDialog.value = false;
        selectedRoles.value = [];
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete selected roles', life: 3000 });
    }
}

function confirmDeleteRole(selectedRole) {
    role.value = selectedRole;
    deleteRoleDialog.value = true;
}

function confirmDeleteSelected() {
    deleteRolesDialog.value = true;
}

const refresh = async () => {
    try {
        const res = await RoleService.findAll();
        roles.value = res.data;
        toast.add({ severity: 'success', summary: 'Refreshed', detail: 'Role list updated', life: 2000 });
    } catch (err) {
        console.error(err);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to refresh roles', life: 3000 });
    }
}

</script>

<template>
    <div>
        <div class="card">
            <Toolbar class="mb-6">
                <template #start>
                    <Button label="New" icon="pi pi-plus" severity="secondary" class="mr-2" @click="openNew" />
                    <Button label="Delete" icon="pi pi-trash" severity="secondary" @click="confirmDeleteSelected()"
                        :disabled="!selectedRoles || !selectedRoles.length" />
                    <Button label="Refresh" icon="pi pi-refresh" severity="secondary" class="ml-2" @click="refresh" />
                </template>

                <template #end>
                    <Button label="Export" icon="pi pi-upload" severity="secondary" @click="exportCSV($event)" />
                </template>
            </Toolbar>

            <DataTable ref="dt" v-model:selection="selectedRoles" :value="roles" dataKey="code" :paginator="true"
                :rows="10" :filters="filters" :globalFilterFields="['code', 'name', 'email']"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} roles">
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Manage Roles</h4>
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Search..." />
                        </IconField>
                    </div>
                </template>
                <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>
                <Column field="code" header="Code" sortable style="min-width: 12rem"></Column>
                <Column field="name" header="Name" sortable style="min-width: 12rem"></Column>
                <Column field="description" header="Description" sortable style="min-width: 15rem"></Column>
                <Column field="created_at" header="Created at" sortable style="min-width: 16rem"></Column>
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editRole(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger"
                            @click="confirmDeleteRole(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="roleDialog" :style="{ width: '450px' }" header="Role Details" :modal="true">
            <div class="flex flex-col gap-6">
                <div>
                    <label for="code" class="block font-bold mb-3">Role code</label>
                    <InputText id="code" v-model.trim="role.code" required="true" autofocus
                        :invalid="submitted && !role.code" fluid />
                    <small v-if="submitted && !role.code" class="text-red-500">Code is required.</small>
                </div>
                <div>
                    <label for="name" class="block font-bold mb-3">Role name</label>
                    <InputText id="name" v-model.trim="role.name" required="true" autofocus
                        :invalid="submitted && !role.name" fluid />
                    <small v-if="submitted && !role.name" class="text-red-500">Name is required.</small>
                </div>
                <div>
                    <label for="description" class="block font-bold mb-3">Description</label>
                    <InputText id="description" v-model.trim="role.description" required="true" autofocus
                        :invalid="submitted && !role.description" fluid />
                    <small v-if="submitted && !role.description" class="text-red-500">Description is required.</small>
                </div>
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" @click="saveRole()" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteRoleDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="role">Are you sure you want to delete <b>{{ role.name }}</b> role?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteRoleDialog = false" />
                <Button label="Yes" icon="pi pi-check" @click="deleteRole" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteRolesDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="roles">Are you sure you want to delete the selected roles?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteRolesDialog = false" />
                <Button label="Yes" icon="pi pi-check" text @click="deleteSelectedRoles" />
            </template>
        </Dialog>
    </div>
</template>
