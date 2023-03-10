/*
Made by shj at xenosi.de
License: MIT
https://github.com/crucifyer/json-safe-stringify
 */
(function(w, d) {
	if(!w.XenoJson) w.XenoJson = {};

	function getClassType(obj) {
		return Object.prototype.toString.call(obj).slice(8, -1).toLowerCase();
	}

	function JSONRemoveCircular(targetObject) {
		let objkeys = [targetObject], keynames = ['o'];

		function recursive(obj, keyname) {
			switch (getClassType(obj)) {
				case 'array':
				case 'object':
					for (let x in obj) {
						let classType = getClassType(obj[x]);
						if(classType != 'array' && classType != 'object') continue;
						let idx = objkeys.indexOf(obj[x]);
						if (idx > -1) {
							obj[x] = '__circularObject__' + keynames[idx];
							return;
						}
						objkeys.push(obj[x]);
						keynames.push(keyname + ':' + x);
						recursive(obj[x], keyname + ':' + x);
					}
					break;
			}
		}

		recursive(targetObject, 'o');

		return true;
	}
	w.XenoJson.RemoveCircular = JSONRemoveCircular;

	function JSONRestoreCircular(targetObject) {
		function returnObjWithKeys(obj, keys) {
			if (keys.length) {
				let key = keys.shift();
				return returnObjWithKeys(obj[key], keys);
			}
			return obj;
		}

		function recursive(obj) {
			switch (getClassType(obj)) {
				case 'array':
				case 'object':
					for (let x in obj) {
						switch (getClassType(obj[x])) {
							default:
								recursive(obj[x]);
								break;
							case 'string':
								if (/^__circularObject__/.test(obj[x])) {
									obj[x] = returnObjWithKeys(targetObject, obj[x].substring(18).split(':').splice(1));
								}
								break;
						}
					}
					break;
			}
		}

		recursive(targetObject);

		return true;
	}
	w.XenoJson.RestoreCircular = JSONRestoreCircular;

	function ObjectFullCopy(targetObject) {
		JSONRemoveCircular(targetObject)
		let jsonText = JSON.stringify(targetObject);
		JSONRestoreCircular(targetObject);
		let res = JSON.parse(jsonText);
		JSONRestoreCircular(res);
		return res;
	}
	w.XenoJson.ObjectFullCopy = ObjectFullCopy;
})(window, document);