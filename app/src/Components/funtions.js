// import $ from "min-jquery";

export function API_URL(url) {
  return "http://localhost/OSD_API/public/api" + url;
}

// export var ACCESS_TOKEN = "";
// export var REFRESH_TOKEN = "";

// export function saveToken(tokens) {
//   localStorage.setItem(USER_KEY, JSON.stringify(tokens));
// }

// export function saveRefreshToken() {
//   return JSON.parse(localStorage.getItem(USER_KEY));
// }

// export function deleteUser() {
//   localStorage.removeItem(USER_KEY);
// }

export async function getRequest(url) {
  let response = await fetch(API_URL(url), {
    method: "GET",
    mode: "cors",
    cache: "no-cache",
    credentials: "same-origin",
    headers: {
      "Content-Type": "application/json",
      "Access-Control-Allow-Origin": "*",
      // Authorization: localStorage.token,
    },
    redirect: "follow",
    referrer: "no-referrer",
  });

  return response;
}

export async function postFormData(url, form) {
  let response = await fetch(API_URL(url), {
    method: "POST",
    mode: "cors",
    cache: "no-cache",
    credentials: "same-origin",
    headers: {
      // "Content-Type": "application/json",
      "Access-Control-Allow-Origin": "*",
      Authorization: localStorage.token,
    },
    redirect: "follow",
    referrer: "no-referrer",
    body: form,
  });

  return response;
}

export async function putFormData(url, form) {
  let response = await fetch(API_URL(url), {
    method: "PUT",
    mode: "cors",
    cache: "no-cache",
    credentials: "same-origin",
    headers: {
      // "Content-Type": "application/json",
      "Access-Control-Allow-Origin": "*",
      Authorization: localStorage.token,
    },
    redirect: "follow",
    referrer: "no-referrer",
    body: form,
  });

  return response;
}

// export async function ajaxPost(url, json) {
//   let response = await $.ajax(API_URL(url), {
//     method: "POST",
//     mode: "cors",
//     cache: "no-cache",
//     credentials: "same-origin",
//     headers: {
//       Accept: "application/json",
//       "Content-Type": "application/json",
//       // "Access-Control-Allow-Origin": "*",
//     },
//     redirect: "follow",
//     referrer: "no-referrer",
//     body: JSON.stringify(json),
//   });

//   return response;
// }

export async function postJSON(url, json) {
  let response = await fetch(API_URL(url), {
    method: "POST",
    mode: "cors",
    cache: "no-cache",
    credentials: "same-origin",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      // "Access-Control-Allow-Origin": "*",
    },
    redirect: "follow",
    referrer: "no-referrer",
    body: JSON.stringify(json),
  });

  return response;
}

export async function putJSON(url, json) {
  let response = await fetch(API_URL(url), {
    method: "PUT",
    mode: "cors",
    cache: "no-cache",
    credentials: "same-origin",
    headers: {
      "Content-Type": "application/json",
      "Access-Control-Allow-Origin": "*",
      Authorization: localStorage.token,
    },
    redirect: "follow",
    referrer: "no-referrer",
    body: JSON.stringify(json),
  });

  return response;
}

export async function deleteJSON(url) {
  let response = await fetch(API_URL(url), {
    method: "DELETE",
    mode: "cors",
    cache: "no-cache",
    credentials: "same-origin",
    headers: {
      "Content-Type": "application/json",
      "Access-Control-Allow-Origin": "*",
      Authorization: localStorage.token,
    },
    redirect: "follow",
    referrer: "no-referrer",
  });

  return response;
}
