<script setup>
import { FilterMatchMode } from "@primevue/core/api";
import { onMounted, ref, watch } from "vue";
import UserService from "@/service/UserService";
import GroupService from "@/service/GroupService";
import RoleService from "@/service/RoleService";
import { useToast } from "primevue";
import FnctionService from "@/service/FnctionService";
import PermissionService from "@/service/PermissionService";

// Primevue
const toast = useToast();

// Data
const users = ref([]);
const groups = ref([]);
const roles = ref([]);
const fnctions = ref([]);
const permissions = ref([]);

// Form model
const user = ref({});
const groupCodes = ref([]); // selected groups
const roleCodes = ref([]); // selected roles
const fnctionCodes = ref([]); // selected functions
const permissionCodes = ref([]);
const fnctionPermissions = ref([]);

const dt = ref();
const deleteUserDialog = ref(false);
const deleteUsersDialog = ref(false);
const userDialog = ref(false);
const selectedUsers = ref([]);
const submitted = ref(false);

onMounted(async () => {
    await Promise.all([
        fetchUsers(),
        fetchGroups(),
        fetchRoles(),
        fetchFnctions(),
        fetchPermissions(),
    ])
});

const fetchUsers = async () => {
    try {
        const res = await UserService.findAll();
        users.value = res.data;
    } catch (err) {
        console.error("Failed to fetch users:", err);
    }
};
const fetchGroups = async () => {
    try {
        const res = await GroupService.findAll();
        groups.value = res.data;
    } catch (err) {
        console.error("Failed to fetch users:", err);
    }
};
const fetchRoles = async () => {
    try {
        const res = await RoleService.findAll();
        roles.value = res.data;
    } catch (err) {
        console.error("Failed to fetch users:", err);
    }
};
const fetchFnctions = async () => {
    try {
        const res = await FnctionService.findAll();
        fnctions.value = res.data;
    } catch (err) {
        console.error("Failed to fetch users:", err);
    }
};
const fetchPermissions = async () => {
    try {
        const res = await PermissionService.findAll();
        permissions.value = res.data;
    } catch (err) {
        console.error("Failed to fetch permissions:", err);
    }
};

const filters = ref({
    global: { value: null, matchMode: FilterMatchMode.CONTAINS },
});

function exportCSV() {
    dt.value.exportCSV();
}

function openNew() {
    user.value = {};
    groupCodes.value = [];
    roleCodes.value = [];
    fnctionCodes.value = [];
    permissionCodes.value = [];
    submitted.value = false;
    userDialog.value = true;
}
function hideDialog() {
    userDialog.value = false;
    submitted.value = false;
}

function editUser(selectedUser) {

    console.log(selectedUser)
    user.value = { ...selectedUser, password: "" };
    groupCodes.value = selectedUser.groups?.map(g => g.code) || [];
    roleCodes.value = selectedUser.roles?.map(r => r.code) || [];
    fnctionCodes.value = selectedUser.fnctions?.map(f => f.code) || [];
    permissionCodes.value = selectedUser.permissions?.map(p => p.code) || [];

    userDialog.value = true;
}

function findIndexById(id) {
    return users.value.findIndex(u => u.id === id);
}


async function saveUser() {
    await refresh();
    submitted.value = true;

    if (
        !user.value.name ||
        !user.value.email ||
        (!user.value.id && !user.value.password)
    )
        return;

    const payload = {
        name: user.value.name,
        email: user.value.email,
        password: user.value.password || undefined,
        group_code: groupCodes.value,
        role_code: roleCodes.value,
        fnction_code: fnctionCodes.value,
        fnciton_permission: fnctionPermissions.value,

    };

    try {
        if (!user.value.id) {
            // Add the newly created user to the list
            const res = await UserService.create(payload);
            users.value.push(res.data);
            console.log(user.value);
            toast.add({
                severity: "success",
                summary: "Success",
                detail: "User Created",
                life: 3000,
            });
        } else {
            // Update existing user
            const res = await UserService.update(user.value.id, payload);
            const index = findIndexById(user.value.id);
            users.value[index] = res.data;

            toast.add({
                severity: "success",
                summary: "Success",
                detail: "User Updated",
                life: 3000,
            });
        }

        // Reset form
        userDialog.value = false;
        user.value = {};
        submitted.value = false;
    } catch (error) {
        console.error(error);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: error.response?.data?.message || "Failed to save user",
            life: 3000,
        });
    }
}

async function deleteUser() {
    try {
        await UserService.delete(user.value.id);
        users.value = users.value.filter(u => u.id !== user.value.id);
        refresh();
        toast.add({
            severity: "success",
            summary: "Successful",
            detail: "User Deleted",
            life: 3000,
        });
        deleteUserDialog.value = false;
        user.value = {};
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to delete user",
            life: 3000,
        });
    }
}

async function deleteSelectedUsers() {
    try {
        const ids = selectedUsers.value.map((u) => u.id);
        await Promise.all(ids.map((id) => UserService.delete(id))); // call API for each
        users.value = users.value.filter((u) => !ids.includes(u.id));
        toast.add({
            severity: "success",
            summary: "Successful",
            detail: "Users Deleted",
            life: 3000,
        });
        deleteUsersDialog.value = false;
        selectedUsers.value = [];
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to delete selected users",
            life: 3000,
        });
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
        toast.add({
            severity: "success",
            summary: "Refreshed",
            detail: "User list updated",
            life: 2000,
        });
    } catch (err) {
        console.error(err);
        toast.add({
            severity: "error",
            summary: "Error",
            detail: "Failed to refresh users",
            life: 3000,
        });
    }
};

watch(fnctionCodes, (newFns) => {
    fnctionPermissions.value = {};
    newFns.forEach(fnCode => {
        const fnObj = fnctions.value.find(f => f.code === fnCode);
        if (fnObj?.permissions) {
            fnctionPermissions.value[fnCode] = fnObj.permissions.map(p => p.code);
        } else {
            fnctionPermissions.value[fnCode] = []; // fallback empty array
        }
    });
}, { immediate: true });

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
                <Column field="id" header="ID" sortable style="min-width: 5rem"></Column>
                <Column field="name" header="Name" sortable style="min-width: 8rem"></Column>
                <Column field="email" header="Email" sortable style="min-width: 10rem"></Column>
                <Column field="created_at" header="Created at" sortable style="min-width: 10rem"></Column>
                <Column header="Groups" sortable>
                    <template #body="slotProps">
                        <span v-if="slotProps.data.groups?.length">
                            {{slotProps.data.groups.map(g => g.code).join(', ')}}
                        </span>
                        <span v-else>-</span>
                    </template>
                </Column>
                <Column header="Roles" sortable>
                    <template #body="slotProps">
                        <span v-if="slotProps.data.roles?.length">
                            {{slotProps.data.roles.map(r => r.code).join(", ")}}
                        </span>
                        <span v-else>-</span>
                    </template>
                </Column>
                <Column header="Functions" sortable>
                    <template #body="slotProps">
                        <span v-if="slotProps.data.fnctions?.length">
                            {{slotProps.data.fnctions.map(f => f.code).join(', ')}}
                        </span>
                        <span v-else>-</span>
                    </template>
                </Column>

                <Column :exportable="false" style="min-width: 8rem">
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
                    <InputText id="password" type="password" v-model.trim="user.password" required="true" autofocus
                        :invalid="submitted && !user.password && !user.id" fluid />
                    <small v-if="submitted && !user.password && !user.id" class="text-red-500">Password is
                        required.</small>
                </div>
                <div>
                    <label for="group" class="font-bold mr-3">Groups</label>
                    <MultiSelect v-model="groupCodes" display="chip" :options="groups" optionLabel="name"
                        optionValue="code" placeholder="Groups" />
                </div>
                <div>
                    <label for="role" class="font-bold mr-3">Roles</label>
                    <MultiSelect v-model="roleCodes" display="chip" :options="roles" optionLabel="name"
                        optionValue="code" placeholder="Roles" />
                </div>
                <div>
                    <label for="fnction" class="font-bold mr-3">Functions</label>
                    <MultiSelect v-model="fnctionCodes" display="chip" :options="fnctions" optionLabel="name"
                        optionValue="code" placeholder="Functions" />
                    <div v-for="fnCode in fnctionCodes" :key="fnCode" class="ml-4">
                    </div>
                </div>
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
