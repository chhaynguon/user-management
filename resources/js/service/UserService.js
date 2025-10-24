import axios from "axios"
const baseURL = '/users';

export default{
    findAll:() => {
        return axios.get(`${baseURL}`)
    }
}