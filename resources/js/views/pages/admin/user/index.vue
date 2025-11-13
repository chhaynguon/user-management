<script setup>
import { FilterMatchMode } from '@primevue/core/api';
import { onMounted, ref } from 'vue';
import UserService from '@/service/UserService';
import { useToast } from 'primevue';

const toast = useToast();
const users = ref([]);
const user = ref();
const deleteUserDialog = ref(false);
const deleteUsersDialog = ref(false);
const userDialog = ref(false);
const dt = ref();
const selectedUsers = ref([]);
const submitted = ref(false);

onMounted(async () => {
    await fetchUsers();
});

const fetchUsers = async () => {
    try {
        const res = await UserService.findAll();
        console.log(res)
        users.value = res.data;
        console.log("Users:", users.value)
    } catch (err) {
        console.error('Failed to fetch users:', err);
    }
}

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS }
});

function exportCSV() {
    dt.value.exportCSV();
}

function openNew() {
    user.value = {};
    submitted.value = false;
    userDialog.value = true;
}
function hideDialog() {
    userDialog.value = false;
    submitted.value = false;
}

function editUser(selectedUser) {
    user.value = { ...selectedUser };
    userDialog.value = true;
}

function findIndexById(id) {
    let index = -1;
    for (let i = 0; i < users.value.length; i++) {
        if (users.value[i].id === id) {
            index = i;
            break;
        }
    }

    return index;
}

async function saveUser() {
    submitted.value = true;

    if (user.value.name?.trim() && user.value.email?.trim() && user.value.password?.trim()) {
        try {
            if (!user.value.id) {
                // Create new user
                const res = await UserService.create({
                    name: user.value.name,
                    email: user.value.email,
                    password: user.value.password,
                });

                // Add the newly created user to the list
                users.value.push(res.data);

                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: 'User Created',
                    life: 3000
                });
            } else {
                // Update existing user
                const res = await UserService.update(user.value.id, {
                    name: user.value.name,
                    email: user.value.email,
                    password: user.value.password,
                });

                const index = findIndexById(user.value.id);
                users.value[index] = res.data;

                toast.add({
                    severity: 'success',
                    summary: 'Success',
                    detail: 'User Updated',
                    life: 3000
                });
            }

            // Reset form
            userDialog.value = false;
            user.value = {};
            submitted.value = false;

        } catch (error) {
            console.error(error);
            toast.add({
                severity: 'error',
                summary: 'Error',
                detail: error.response?.data?.message || 'Failed to save user',
                life: 3000
            });
        }
    }
}

function deleteUser() {
    try {
        UserService.delete(user.value.id); // make sure UserService has a delete method
        users.value = users.value.filter(u => u.id !== user.value.id);
        refresh();
        toast.add({ severity: 'success', summary: 'Successful', detail: 'User Deleted', life: 3000 });
        deleteUserDialog.value = false;
        user.value = {};
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete user', life: 3000 });
    }

}

function deleteSelectedUsers() {
    try {
        const ids = selectedUsers.value.map(u => u.id);
        Promise.all(ids.map(id => UserService.delete(id))); // call API for each
        users.value = users.value.filter(u => !ids.includes(u.id));
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Users Deleted', life: 3000 });
        deleteUsersDialog.value = false;
        selectedUsers.value = [];
    } catch (error) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to delete selected users', life: 3000 });
    }
}

function confirmDeleteUser(selectedUser) {
    user.value = selectedUser;
    deleteUserDialog.value = true;
}

function confirmDeleteSelected() {
    deleteUsersDialog.value = true;
}

const refresh = async () => {
    try {
        const res = await UserService.findAll();
        users.value = res.data;
        toast.add({ severity: 'success', summary: 'Refreshed', detail: 'User list updated', life: 2000 });
    } catch (err) {
        console.error(err);
        toast.add({ severity: 'error', summary: 'Error', detail: 'Failed to refresh users', life: 3000 });
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
                        :disabled="!selectedUsers || !selectedUsers.length" />
                    <Button label="Refresh" icon="pi pi-refresh" severity="secondary" class="ml-2" @click="refresh" />
                </template>

                <template #end>
                    <Button label="Export" icon="pi pi-upload" severity="secondary" @click="exportCSV($event)" />
                </template>
            </Toolbar>

            <DataTable ref="dt" v-model:selection="selectedUsers" :value="users" dataKey="id" :paginator="true"
                :rows="10" :filters="filters" :globalFilterFields="['id', 'name', 'email']"
                paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                :rowsPerPageOptions="[5, 10, 25]"
                currentPageReportTemplate="Showing {first} to {last} of {totalRecords} users">
                <template #header>
                    <div class="flex flex-wrap gap-2 items-center justify-between">
                        <h4 class="m-0">Manage Users</h4>
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="filters['global'].value" placeholder="Search..." />
                        </IconField>
                    </div>
                </template>
                <Column selectionMode="multiple" style="width: 3rem" :exportable="false"></Column>
                <Column field="id" header="ID" sortable style="min-width: 12rem"></Column>
                <Column field="name" header="Name" sortable style="min-width: 16rem"></Column>
                <Column field="email" header="Email" sortable style="min-width: 16rem"></Column>
                <Column field="created_at" header="Created at" sortable style="min-width: 16rem"></Column>
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button icon="pi pi-pencil" outlined rounded class="mr-2" @click="editUser(slotProps.data)" />
                        <Button icon="pi pi-trash" outlined rounded severity="danger"
                            @click="confirmDeleteUser(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <Dialog v-model:visible="userDialog" :style="{ width: '450px' }" header="User Details" :modal="true">
            <div class="flex flex-col gap-6">
                <div>
                    <label for="name" class="block font-bold mb-3">Username</label>
                    <InputText id="name" v-model.trim="user.name" required="true" autofocus
                        :invalid="submitted && !user.name" fluid />
                    <small v-if="submitted && !user.name" class="text-red-500">Name is required.</small>
                </div>
                <div>
                    <label for="email" class="block font-bold mb-3">Email</label>
                    <InputText id="email" v-model.trim="user.email" required="true" autofocus
                        :invalid="submitted && !user.email" fluid />
                    <small v-if="submitted && !user.email" class="text-red-500">Email is required.</small>
                </div>
                <div>
                    <label for="password" class="block font-bold mb-3">Password</label>
                    <InputText id="password" v-model.trim="user.password" required="true" autofocus
                        :invalid="submitted && !user.password" fluid />
                    <small v-if="submitted && !user.password" class="text-red-500">Name is required.</small>
                </div>
                <!-- <div>
                    <label for="email" class="block font-bold mb-3">Email</label>
                    <InputText id="email" v-model="user.email" fluid />
                </div>
                <div>
                    <label for="password" class="block font-bold mb-3">Password</label>
                    <InputText id="password" v-model="user.password" integeronly fluid />
                </div> -->
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times" text @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" @click="saveUser()" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteUserDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="user">Are you sure you want to delete <b>{{ user.name }}</b>?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteUserDialog = false" />
                <Button label="Yes" icon="pi pi-check" @click="deleteUser" />
            </template>
        </Dialog>

        <Dialog v-model:visible="deleteUsersDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
            <div class="flex items-center gap-4">
                <i class="pi pi-exclamation-triangle !text-3xl" />
                <span v-if="users">Are you sure you want to delete the selected users?</span>
            </div>
            <template #footer>
                <Button label="No" icon="pi pi-times" text @click="deleteUsersDialog = false" />
                <Button label="Yes" icon="pi pi-check" text @click="deleteSelectedUsers" />
            </template>
        </Dialog>
    </div>
</template>
