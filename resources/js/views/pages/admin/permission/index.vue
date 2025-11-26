<script setup>
import { FilterMatchMode } from '@primevue/core/api';
import { onMounted, ref } from 'vue';
import PermissionService from '@/service/PermissionService';
import { useToast } from 'primevue';

const toast = useToast();
const permissions = ref([]);
const permission = ref();
const deletePermissionDialog = ref(false);
const deletePermissionsDialog = ref(false);
const permissionDialog = ref(false);
const dt = ref();
const selectedPermissions = ref([]);
const submitted = ref(false);

onMounted(async () => {
    await fetchPermissions();
});

const fetchPermissions = async () => {
    try {
        const res = await PermissionService.findAll();
        permissions.value = res.data;
        console.log("Permissions:", permissions.value)
    } catch (err) {
        console.error('Failed to fetch permissions:', err);
    }
}

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS }
});

function exportCSV() {
    dt.value.exportCSV();
}

function openNew() {
    permission.value = {};
    submitted.value = false;
    permissionDialog.value = true;
}
function hideDialog() {
    permissionDialog.value = false;
    submitted.value = false;
}

function editPermission(selectedPermission) {
    permission.value = { ...selectedPermission, originalCode: selectedPermission.code };
    permissionDialog.value = true;
}


async function savePermission() {
    submitted.value = true;

    if (!permission.value.code?.trim() || !permission.value.name?.trim() || !permission.value.description?.trim()) {
        return;
    }
    try {
        const { originalCode } = permission.value;
        if (!originalCode) {
            if (permissions.value.some(p => p.code === permission.value.code)) {
                toast.add({ severity: 'error', summary: 'Error', detail: 'Permission code already exists', life: 3000 });
                return;
            }
            // Create new permission
            const res = await PermissionService.create({
                code: permission.value.code,
                name: permission.value.name,
                description: permission.value.description,
            });

            // Add the newly created permission to the list
            permissions.value.push(res.data);

            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Permission Created',
                life: 3000
            });
        } else {
            // Update existing permission
            const res = await PermissionService.update(permission.value.code, {
                code: permission.value.code,
                name: permission.value.name,
                description: permission.value.description,
            });

            const index = permissions.value.findIndex(p => p.code === originalCode);
            if (index !== -1) permissions.value[index] = res.data;

            toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Permission Updated',
                life: 3000
            });
        }

        // Reset form
        permissionDialog.value = false;
        permission.value = {};
        submitted.value = false;

    } catch (error) {
        console.error(error);
        toast.add({
            severity: 'error',
            summary: 'Error',
            detail: error.response?.data?.message || 'Failed to save permission',
            life: 3000
        });
    }
}

async function deletePermission() {
    try {
        await PermissionService.delete(permission.value.code); // make sure PermissionService has a delete method
        permissions.value = permissions.value.filter(u => u.code !== permission.value.code);
        selectedPermissions.value = [];
        deletePermissionDialog.value = false;
        permission.value = {};
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Permission Deleted', life: 3000 });
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete permission', life: 3000 });
    }

}

async function deleteSelectedPermissions() {
    try {
        const codes = selectedPermissions.value.map(u => u.code);
        await Promise.all(codes.map(code => PermissionService.delete(code))); // call API for each
        permissions.value = permissions.value.filter(u => !codes.includes(u.code));
        deletePermissionsDialog.value = false;
        selectedPermissions.value = [];
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Permissions Deleted', life: 3000 });
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete selected permissions', life: 3000 });
    }
}

function confirmDeletePermission(selectedPermission) {
    permission.value = selectedPermission;
    deletePermissionDialog.value = true;
}

function confirmDeleteSelected() {
    deletePermissionsDialog.value = true;
}

const refresh = async () => {
    try {
        await fetchPermissions();
        toast.add({ severity: 'success', summary: 'Refreshed', detail: 'Permission list updated', life: 2000 });
    } catch (err) {
        console.error(err);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to refresh permissions', life: 3000 });
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
                        :disabled="!selectedPermissions || !selectedPermissions.length" />
                    <Button label="Refresh" icon="pi pi-refresh" severity="secondary" class="ml-2" @click="refresh" />
                </template>

                <template #end>
                    <Button label="Export" icon="pi pi-upload" severity="secondary" @click="exportCSV($event)" />
                </template>
            </Toolbar>

            <DataTable ref="dt" v-model:selection="selectedPermissions" :value="permissions" dataKey="code"
                :paginator="true" :rows="10" :filters="filters" :globalFilterFields="['code', 'name', 'email']"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} permissions">
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Manage Permissions</h4>
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
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2"
                            @click="editPermission(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger"
                            @click="confirmDeletePermission(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="permissionDialog" :style="{ width: '450px' }" header="Permission Details"
            :modal="true">
            <div class="flex flex-col gap-6">
                <div>
                    <label for="code" class="block font-bold mb-3">Permission code</label>
                    <InputText id="code" v-model.trim="permission.code" required autofocus
                        :invalid="submitted && !permission.code" fluid :readonly="!!permission.originalCode" />
                </div>
                <div>
                    <label for="name" class="block font-bold mb-3">Permission name</label>
                    <InputText id="name" v-model.trim="permission.name" required="true" autofocus
                        :invalid="submitted && !permission.name" fluid />
                    <small v-if="submitted && !permission.name" class="text-red-500">Name is required.</small>
                </div>
                <div>
                    <label for="description" class="block font-bold mb-3">Description</label>
                    <InputText id="description" v-model.trim="permission.description" required="true" autofocus
                        :invalid="submitted && !permission.description" fluid />
                    <small v-if="submitted && !permission.description" class="text-red-500">Description is
                        required.</small>
                </div>
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" @click="savePermission()" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deletePermissionDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="permission">Are you sure you want to delete <b>{{ permission.name }}</b> permission?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deletePermissionDialog = false" />
                <Button label="Yes" icon="pi pi-check" @click="deletePermission" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deletePermissionsDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="permissions">Are you sure you want to delete the selected permissions?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deletePermissionsDialog = false" />
                <Button label="Yes" icon="pi pi-check" text @click="deleteSelectedPermissions" />
            </template>
        </Dialog>
    </div>
</template>
